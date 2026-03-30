<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';
include __DIR__ . '/../../backend/lib/event_approval_otp.php';
include __DIR__ . '/../../backend/lib/sms_sender.php';
include __DIR__ . '/../../backend/lib/email_sender.php';

// Only admin or super_admin can change event status
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php");
    exit();
}

if (!csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Invalid request. Please try again."));
    exit();
}

$eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$action  = $_POST['action'] ?? '';

$validActions = ['approve', 'approve_with_otp', 'send_otp', 'reject', 'close'];
if ($eventId <= 0 || !in_array($action, $validActions, true)) {
    header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Invalid request."));
    exit();
}

// Only super_admin can close events
if ($action === 'close' && $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Only Super Admin can close events."));
    exit();
}

// Map action to new status
if ($action === 'approve' || $action === 'approve_with_otp') {
    $newStatus = 'active';
} elseif ($action === 'reject') {
    $newStatus = 'rejected';
} else {
    $newStatus = 'closed';
}

if ($action === 'send_otp') {
    if (!eventify_event_otp_table_ready($conn)) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("OTP table missing. Run school_events_event_approval_otp.sql first."));
        exit();
    }

    $evStmt = $conn->prepare("SELECT e.id, e.organizer_id, e.title, e.status, u.email, u.organizer_contact_email, u.organizer_phone, u.organizer_contact_method FROM events e JOIN users u ON e.organizer_id = u.id WHERE e.id = ?");
    if (!$evStmt) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("Failed to prepare OTP request."));
        exit();
    }
    $evStmt->bind_param("i", $eventId);
    $evStmt->execute();
    $ev = $evStmt->get_result()->fetch_assoc();
    $evStmt->close();
    if (!$ev || ($ev['status'] ?? '') !== 'pending') {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("OTP can only be sent for pending events."));
        exit();
    }

    $deliveryMethod = ($ev['organizer_contact_method'] ?? 'email') === 'phone' ? 'phone' : 'email';
    $deliveryTarget = '';
    // Always prefer account email as canonical OTP email target.
    $fallbackEmail = trim((string) ($ev['email'] ?? ''));
    if ($fallbackEmail === '') {
        $fallbackEmail = trim((string) ($ev['organizer_contact_email'] ?? ''));
    }
    if ($deliveryMethod === 'phone') {
        $deliveryTarget = trim((string) ($ev['organizer_phone'] ?? ''));
        if ($deliveryTarget === '') {
            $deliveryMethod = 'email';
        }
    }
    if ($deliveryMethod === 'email') {
        $deliveryTarget = trim((string) ($ev['email'] ?? ''));
        if ($deliveryTarget === '') {
            $deliveryTarget = trim((string) ($ev['organizer_contact_email'] ?? ''));
        }
    }
    if ($deliveryTarget === '') {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("Organizer has no OTP contact set in profile."));
        exit();
    }

    $otpCode = eventify_generate_otp_code(6);
    $otpHash = password_hash($otpCode, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + (10 * 60));
    $organizerId = (int) ($ev['organizer_id'] ?? 0);
    $adminId = (int) ($_SESSION['user_id'] ?? 0);

    $invalidate = $conn->prepare("UPDATE event_approval_otps SET used_at = NOW() WHERE event_id = ? AND used_at IS NULL");
    if ($invalidate) {
        $invalidate->bind_param("i", $eventId);
        $invalidate->execute();
        $invalidate->close();
    }
    $otpIns = $conn->prepare("INSERT INTO event_approval_otps (event_id, organizer_id, delivery_method, delivery_target, otp_hash, expires_at, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($otpIns) {
        $otpIns->bind_param("iissssi", $eventId, $organizerId, $deliveryMethod, $deliveryTarget, $otpHash, $expiresAt, $adminId);
        $otpIns->execute();
        $otpIns->close();
    }

    $title = 'Event approval OTP';
    $msg = 'Your OTP for event "' . ($ev['title'] ?? 'Event') . '" is ' . $otpCode . '. It expires in 10 minutes.';
    $ins = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'event_approval_otp', ?, ?, ?)");
    if ($ins) {
        $ins->bind_param("issi", $organizerId, $title, $msg, $eventId);
        $ins->execute();
        $ins->close();
    }

    $deliveredLabel = 'in-app notification';
    $deliveryNote = '';
    if ($deliveryMethod === 'email') {
        $subject = '[EVENTIFY] Event approval OTP';
        $body = $msg . "\n\nIf you did not request this, contact admin.";
        $emailResult = eventify_send_email($deliveryTarget, $subject, $body);
        if (!empty($emailResult['ok'])) {
            $deliveredLabel = 'email + in-app notification';
        } else {
            $deliveryNote = ' Email failed: ' . ($emailResult['error'] ?? 'unknown error');
        }
    } elseif ($deliveryMethod === 'phone') {
        $normalizedPhone = eventify_normalize_ph_phone($deliveryTarget);
        if ($normalizedPhone !== '') {
            $smsResult = eventify_send_sms_semaphore($normalizedPhone, $msg);
            if (!empty($smsResult['ok'])) {
                $deliveredLabel = 'phone SMS + in-app notification';
            } else {
                $deliveryNote = ' SMS failed: ' . ($smsResult['error'] ?? 'unknown error');
                if ($fallbackEmail !== '') {
                    $subject = '[EVENTIFY] Event approval OTP';
                    $body = $msg . "\n\nSMS delivery failed, so this OTP was sent by email.";
                    $fallbackEmailResult = eventify_send_email($fallbackEmail, $subject, $body);
                    if (!empty($fallbackEmailResult['ok'])) {
                        $deliveredLabel = 'email fallback + in-app notification';
                    } else {
                        $deliveryNote .= ' Email fallback failed: ' . ($fallbackEmailResult['error'] ?? 'unknown error');
                    }
                }
            }
        } else {
            $deliveryNote = ' SMS failed: invalid phone number format.';
            if ($fallbackEmail !== '') {
                $subject = '[EVENTIFY] Event approval OTP';
                $body = $msg . "\n\nSMS delivery failed, so this OTP was sent by email.";
                $fallbackEmailResult = eventify_send_email($fallbackEmail, $subject, $body);
                if (!empty($fallbackEmailResult['ok'])) {
                    $deliveredLabel = 'email fallback + in-app notification';
                } else {
                    $deliveryNote .= ' Email fallback failed: ' . ($fallbackEmailResult['error'] ?? 'unknown error');
                }
            }
        }
    }

    log_activity($conn, $adminId, $_SESSION['role'] ?? '', 'event_approval_otp_sent', 'event', $eventId, 'Sent OTP via ' . $deliveryMethod);
    $masked = $deliveryMethod === 'email' ? eventify_mask_email($deliveryTarget) : eventify_mask_phone($deliveryTarget);
    $conn->close();
    header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("OTP sent to {$masked} via {$deliveredLabel}." . $deliveryNote));
    exit();
}

if ($action === 'approve_with_otp') {
    if (!eventify_event_otp_table_ready($conn)) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("OTP table missing. Run school_events_event_approval_otp.sql first."));
        exit();
    }
    $otpCode = trim((string)($_POST['otp_code'] ?? ''));
    if (!preg_match('/^\d{6}$/', $otpCode)) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("Invalid OTP format."));
        exit();
    }

    $otpStmt = $conn->prepare("SELECT id, otp_hash, expires_at FROM event_approval_otps WHERE event_id = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1");
    $otpStmt->bind_param("i", $eventId);
    $otpStmt->execute();
    $otp = $otpStmt->get_result()->fetch_assoc();
    $otpStmt->close();
    if (!$otp) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("No active OTP found. Send OTP first."));
        exit();
    }
    if (strtotime((string)$otp['expires_at']) < time()) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("OTP expired. Send a new OTP."));
        exit();
    }
    if (!password_verify($otpCode, (string)$otp['otp_hash'])) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode("Incorrect OTP."));
        exit();
    }
    $usedBy = (int) ($_SESSION['user_id'] ?? 0);
    $markUsed = $conn->prepare("UPDATE event_approval_otps SET used_at = NOW(), verified_by = ? WHERE id = ?");
    if ($markUsed) {
        $otpId = (int) ($otp['id'] ?? 0);
        $markUsed->bind_param("ii", $usedBy, $otpId);
        $markUsed->execute();
        $markUsed->close();
    }
}

if ($action === 'reject') {
    $reason = trim($_POST['reject_reason'] ?? '');
    $stmt = $conn->prepare("UPDATE events SET status = ?, reject_reason = ? WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Failed to prepare update."));
        exit();
    }
    $stmt->bind_param("ssi", $newStatus, $reason, $eventId);
} else {
    $stmt = $conn->prepare("UPDATE events SET status = ?, reject_reason = NULL WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Failed to prepare update."));
        exit();
    }
    $stmt->bind_param("si", $newStatus, $eventId);
}
if ($stmt->execute()) {
    $stmt->close();

    // Log activity
    $actorId   = $_SESSION['user_id'] ?? null;
    $actorRole = $_SESSION['role'] ?? null;
    $actionKey = ($action === 'approve' || $action === 'approve_with_otp') ? 'event_approved' : ($action === 'reject' ? 'event_rejected' : 'event_closed');
    $details   = ($action === 'approve' || $action === 'approve_with_otp') ? "Approved event ID {$eventId}" : ($action === 'reject' ? "Rejected event ID {$eventId}" : "Closed event ID {$eventId}");
    log_activity($conn, $actorId, $actorRole, $actionKey, 'event', $eventId, $details);

    // Notify organizer when event is approved or rejected
    if (in_array($action, ['approve', 'approve_with_otp', 'reject'], true)) {
        $evStmt = $conn->prepare("SELECT e.title, e.organizer_id, e.reject_reason, u.email FROM events e JOIN users u ON e.organizer_id = u.id WHERE e.id = ?");
        $evStmt->bind_param("i", $eventId);
        $evStmt->execute();
        $ev = $evStmt->get_result();
        $evStmt->close();
        if ($ev && $row = $ev->fetch_assoc()) {
            $organizerId = (int)$row['organizer_id'];
            $eventTitle  = $row['title'] ?? 'Event';
            $organizerEmail = $row['email'] ?? '';
            $approvedAction = in_array($action, ['approve', 'approve_with_otp'], true);
            $notifType   = $approvedAction ? 'event_approved' : 'event_rejected';
            $notifTitle  = $approvedAction ? 'Event approved' : 'Event rejected';
            $notifMsg    = $approvedAction
                ? ('Your event "' . $eventTitle . '" has been approved and is now visible to students.')
                : ('Your event "' . $eventTitle . '" was rejected.' . ($row['reject_reason'] ? ' Reason: ' . $row['reject_reason'] : ''));
            $ins = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, ?, ?, ?, ?)");
            if ($ins) {
                $ins->bind_param("isssi", $organizerId, $notifType, $notifTitle, $notifMsg, $eventId);
                $ins->execute();
                $ins->close();
            }
            // Optional: send email to organizer via SMTP helper
            if ($organizerEmail) {
                $subject = '[EVENTIFY] ' . $notifTitle . ': ' . $eventTitle;
                $body    = $notifMsg . "\n\nLog in to your organizer dashboard to view details.";
                eventify_send_email($organizerEmail, $subject, $body);
            }
        }
    }

    $conn->close();

    $msg = ($action === 'approve' || $action === 'approve_with_otp') ? "Event approved." : ($action === 'reject' ? "Event rejected." : "Event closed.");
    $isSuperAdmin = ($_SESSION['role'] ?? '') === 'super_admin';
    $returnToDashboard = !empty($_POST['return_to']) && $_POST['return_to'] === 'dashboard';
    if ($isSuperAdmin) {
        $redirect = BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode($msg);
    } elseif ($_SESSION['role'] === 'admin' && $returnToDashboard) {
        $redirect = BASE_URL . "/backend/admin/dashboard.php?success=" . urlencode($msg);
    } else {
        $redirect = BASE_URL . "/backend/super_admin/manage_events.php?success=" . urlencode($msg);
    }
    header("Location: " . $redirect);
    exit();
}

$stmt->close();
$conn->close();

header("Location: " . BASE_URL . "/backend/super_admin/manage_events.php?error=" . urlencode("Failed to update event status."));
exit();

