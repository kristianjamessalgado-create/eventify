<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/activity_logger.php';
require_once __DIR__ . '/../lib/account_email_otp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Invalid request."));
    exit();
}

$purpose = ($_POST['purpose'] ?? 'register') === 'reactivate' ? 'reactivate' : 'register';
$email = trim(strtolower($_POST['email'] ?? ''));
$otpCode = trim($_POST['otp_code'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otpCode)) {
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("Invalid verification input."));
    exit();
}

if (!eventify_account_otp_table_ready($conn)) {
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("OTP system unavailable."));
    exit();
}

$stmt = $conn->prepare("SELECT id, user_id, otp_hash, payload_json, expires_at FROM account_email_otps WHERE purpose = ? AND email = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $purpose, $email);
$stmt->execute();
$otpRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$otpRow) {
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("No active OTP found."));
    exit();
}
if (strtotime((string)$otpRow['expires_at']) < time()) {
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("OTP expired."));
    exit();
}
if (!password_verify($otpCode, (string)$otpRow['otp_hash'])) {
    // Increment failed attempt counter and invalidate this OTP after too many attempts.
    $otpId = (int)($otpRow['id'] ?? 0);
    $attempts = (int)($otpRow['attempt_count'] ?? 0) + 1;
    $upFail = $conn->prepare("UPDATE account_email_otps SET attempt_count = ? WHERE id = ?");
    if ($upFail) {
        $upFail->bind_param("ii", $attempts, $otpId);
        $upFail->execute();
        $upFail->close();
    }
    if ($attempts >= 5) {
        $expireOtp = $conn->prepare("UPDATE account_email_otps SET used_at = NOW() WHERE id = ? AND used_at IS NULL");
        if ($expireOtp) {
            $expireOtp->bind_param("i", $otpId);
            $expireOtp->execute();
            $expireOtp->close();
        }
        header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("Too many incorrect OTP attempts. Request a new OTP."));
        exit();
    }
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode("Incorrect OTP."));
    exit();
}

$conn->begin_transaction();
try {
    $mark = $conn->prepare("UPDATE account_email_otps SET used_at = NOW() WHERE id = ? AND used_at IS NULL");
    $otpId = (int)($otpRow['id'] ?? 0);
    $mark->bind_param("i", $otpId);
    $mark->execute();
    $mark->close();

    if ($purpose === 'register') {
        $payload = json_decode((string)($otpRow['payload_json'] ?? ''), true);
        if (!is_array($payload)) {
            throw new Exception('Missing registration payload');
        }
        $name = trim((string)($payload['name'] ?? ''));
        $passwordHash = (string)($payload['password_hash'] ?? '');
        $role = (string)($payload['role'] ?? '');
        $department = $payload['department'] ?? null;
        $userIdCode = (string)($payload['user_code'] ?? '');
        if ($name === '' || $passwordHash === '' || $userIdCode === '' || !in_array($role, ['student', 'organizer', 'multimedia', 'admin'], true)) {
            throw new Exception('Invalid registration payload');
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();
        if ($exists) {
            throw new Exception('Email already registered');
        }

        // After OTP verification, all new accounts stay pending until super admin approval.
        $status = 'inactive';
        $ins = $conn->prepare("INSERT INTO users (user_id, name, email, password, role, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssss", $userIdCode, $name, $email, $passwordHash, $role, $department, $status);
        $ins->execute();
        $newUserId = (int)$conn->insert_id;
        $ins->close();
        log_activity($conn, $newUserId, $role, 'register_email_verified', 'user', $newUserId, 'Completed registration via OTP email verification');

        // Notify all active super admins that a new verified account is awaiting approval.
        $saQ = $conn->query("SELECT id FROM users WHERE role = 'super_admin' AND status = 'active'");
        if ($saQ) {
            $notifTitle = 'New account pending approval';
            $notifMsg = 'Email-verified registration waiting approval: ' . $name . ' (' . $role . ')';
            $insNotif = $conn->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'account_pending_approval', ?, ?)");
            if ($insNotif) {
                while ($sa = $saQ->fetch_assoc()) {
                    $superId = (int)($sa['id'] ?? 0);
                    if ($superId > 0) {
                        $insNotif->bind_param("iss", $superId, $notifTitle, $notifMsg);
                        $insNotif->execute();
                    }
                }
                $insNotif->close();
            }
        }

        $success = "Email verified. Registration is now pending super admin approval.";
        $redirect = BASE_URL . "/views/login.php?success=" . urlencode($success) . "&form=login";
    } else {
        $userId = (int)($otpRow['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new Exception('Missing user for reactivation');
        }
        // Reactivation OTP immediately unlocks the account and resets lock attempts.
        $upd = $conn->prepare("UPDATE users SET status = 'active', failed_attempts = 0, must_change_password = 1 WHERE id = ?");
        $upd->bind_param("i", $userId);
        $upd->execute();
        $upd->close();
        // Load role/name for auto-login after OTP verification.
        $uStmt = $conn->prepare("SELECT id, role, name FROM users WHERE id = ? LIMIT 1");
        if (!$uStmt) {
            throw new Exception('Failed to load reactivated account');
        }
        $uStmt->bind_param("i", $userId);
        $uStmt->execute();
        $u = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();
        if (!$u) {
            throw new Exception('Reactivated account not found');
        }

        $_SESSION['user_id'] = (int)($u['id'] ?? 0);
        $_SESSION['role'] = (string)($u['role'] ?? '');
        $_SESSION['name'] = (string)($u['name'] ?? '');
        session_regenerate_id(true);

        $role = (string)($u['role'] ?? '');
        $dashboardRedirect = BASE_URL . "/index.php";
        if ($role === 'super_admin') {
            $dashboardRedirect = BASE_URL . "/backend/super_admin/dashboardsuperadmin.php";
        } elseif ($role === 'admin') {
            $dashboardRedirect = BASE_URL . "/backend/admin/dashboard.php";
        } elseif ($role === 'organizer') {
            $dashboardRedirect = BASE_URL . "/backend/auth/dashboardorganizer.php";
        } elseif ($role === 'student') {
            $dashboardRedirect = BASE_URL . "/backend/auth/dashboard_student.php";
        } elseif ($role === 'multimedia') {
            $dashboardRedirect = BASE_URL . "/backend/auth/dashboard_multimedia.php";
        }
        $redirect = BASE_URL . "/views/change_password.php?from=reactivation&next=" . urlencode($dashboardRedirect);

        log_activity($conn, $userId, $role ?: 'user', 'account_reactivated_by_otp', 'user', $userId, 'User completed reactivation OTP verification and was auto-logged in');
    }

    $conn->commit();
    header("Location: " . $redirect);
    exit();
} catch (Throwable $e) {
    $conn->rollback();
    header("Location: " . BASE_URL . "/views/verify_account_otp.php?purpose={$purpose}&email=" . urlencode($email) . "&error=" . urlencode($e->getMessage()));
    exit();
}
