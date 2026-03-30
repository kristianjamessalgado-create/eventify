<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../backend/lib/activity_logger.php';
require_once __DIR__ . '/../../backend/lib/event_approval_otp.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'organizer') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Invalid request."));
    exit();
}

$organizerId = (int) ($_SESSION['user_id'] ?? 0);
$eventId = (int) ($_POST['event_id'] ?? 0);
$otpCode = trim((string) ($_POST['otp_code'] ?? ''));

if ($eventId <= 0 || !preg_match('/^\d{6}$/', $otpCode)) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Invalid OTP input."));
    exit();
}

if (!eventify_event_otp_table_ready($conn)) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("OTP table missing. Ask admin to run migration."));
    exit();
}

$evStmt = $conn->prepare("SELECT id, title, status FROM events WHERE id = ? AND organizer_id = ?");
if (!$evStmt) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Failed to validate event."));
    exit();
}
$evStmt->bind_param("ii", $eventId, $organizerId);
$evStmt->execute();
$eventRow = $evStmt->get_result()->fetch_assoc();
$evStmt->close();

if (!$eventRow) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Event not found."));
    exit();
}
if (($eventRow['status'] ?? '') !== 'pending') {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("This event is no longer pending."));
    exit();
}

$otpStmt = $conn->prepare("SELECT id, otp_hash, expires_at FROM event_approval_otps WHERE event_id = ? AND organizer_id = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1");
if (!$otpStmt) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("No OTP request found."));
    exit();
}
$otpStmt->bind_param("ii", $eventId, $organizerId);
$otpStmt->execute();
$otpRow = $otpStmt->get_result()->fetch_assoc();
$otpStmt->close();

if (!$otpRow) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("No active OTP found. Ask admin to request OTP."));
    exit();
}
if (strtotime((string) $otpRow['expires_at']) < time()) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("OTP expired. Ask admin to request a new OTP."));
    exit();
}
if (!password_verify($otpCode, (string) $otpRow['otp_hash'])) {
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Incorrect OTP."));
    exit();
}

$conn->begin_transaction();
try {
    $markOtp = $conn->prepare("UPDATE event_approval_otps SET used_at = NOW() WHERE id = ? AND used_at IS NULL");
    if (!$markOtp) {
        throw new Exception('Unable to update OTP');
    }
    $otpId = (int) $otpRow['id'];
    $markOtp->bind_param("i", $otpId);
    $markOtp->execute();
    $markOtp->close();

    $approve = $conn->prepare("UPDATE events SET status = 'active', reject_reason = NULL WHERE id = ? AND organizer_id = ? AND status = 'pending'");
    if (!$approve) {
        throw new Exception('Unable to approve event');
    }
    $approve->bind_param("ii", $eventId, $organizerId);
    $approve->execute();
    $changed = $approve->affected_rows > 0;
    $approve->close();
    if (!$changed) {
        throw new Exception('Event was not updated');
    }

    $eventTitle = (string) ($eventRow['title'] ?? 'Event');
    $notifToOrganizer = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'event_approved', 'Event approved', ?, ?)");
    if ($notifToOrganizer) {
        $msgOrg = 'Your event "' . $eventTitle . '" is now approved and visible to students.';
        $notifToOrganizer->bind_param("isi", $organizerId, $msgOrg, $eventId);
        $notifToOrganizer->execute();
        $notifToOrganizer->close();
    }

    $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin','super_admin') AND status = 'active'");
    if ($admins) {
        $insAdminNotif = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'event_auto_approved', ?, ?, ?)");
        if ($insAdminNotif) {
            $adminTitle = 'Event approved via organizer OTP';
            $adminMsg = 'Organizer verified OTP. Event "' . $eventTitle . '" is now active.';
            while ($adm = $admins->fetch_assoc()) {
                $adminId = (int) ($adm['id'] ?? 0);
                if ($adminId > 0) {
                    $insAdminNotif->bind_param("issi", $adminId, $adminTitle, $adminMsg, $eventId);
                    $insAdminNotif->execute();
                }
            }
            $insAdminNotif->close();
        }
    }

    log_activity($conn, $organizerId, 'organizer', 'event_approved_via_otp', 'event', $eventId, 'Organizer verified OTP and event became active');
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("Failed to verify OTP. Please try again."));
    exit();
}

header("Location: " . BASE_URL . "/backend/auth/dashboardorganizer.php?msg=" . urlencode("OTP verified. Your event is now approved."));
exit();
