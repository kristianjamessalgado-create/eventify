<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';
include __DIR__ . '/../../backend/lib/account_email_otp.php';

// Only super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid request."));
    exit();
}

// Validate ID
if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid user ID"));
    exit();
}

$id = (int)$_POST['id'];

$uStmt = $conn->prepare("SELECT id, email, name, status FROM users WHERE id = ? LIMIT 1");
$uStmt->bind_param("i", $id);
$uStmt->execute();
$user = $uStmt->get_result()->fetch_assoc();
$uStmt->close();
if (!$user) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User not found."));
    exit();
}

if (($user['status'] ?? '') !== 'inactive') {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User is not inactive; reactivation OTP is not required."));
    exit();
}

// Reactivation OTP is intended for locked accounts only (failed attempts threshold reached).
$lockStmt = $conn->prepare("SELECT failed_attempts FROM users WHERE id = ? LIMIT 1");
if ($lockStmt) {
    $lockStmt->bind_param("i", $id);
    $lockStmt->execute();
    $lockRow = $lockStmt->get_result()->fetch_assoc();
    $lockStmt->close();
    $failedAttempts = (int)($lockRow['failed_attempts'] ?? 0);
    if ($failedAttempts < 5) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Cannot send reactivation OTP: account is not locked."));
        exit();
    }
}

$email = trim((string)($user['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Cannot reactivate: invalid email on account."));
    exit();
}

$otpCreate = eventify_create_email_otp($conn, 'reactivate', $email, $id, null, 10);
if (empty($otpCreate['ok'])) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Reactivation OTP failed to generate."));
    exit();
}
$otpSend = eventify_send_account_otp_email($email, 'reactivate', (string)$otpCreate['code']);
if (empty($otpSend['ok'])) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Reactivation OTP email failed: " . ($otpSend['error'] ?? 'unknown error')));
    exit();
}

// Log activity
$actorId   = $_SESSION['user_id'] ?? null;
$actorRole = $_SESSION['role'] ?? null;
$details   = "Sent reactivation OTP to user ID {$id}";
log_activity($conn, $actorId, $actorRole, 'user_reactivated', 'user', (int)$id, $details);

$conn->close();

header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Reactivation OTP sent to " . $email . ". User must verify OTP to activate account."));
exit;
