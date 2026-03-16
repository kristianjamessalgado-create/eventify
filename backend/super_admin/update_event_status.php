<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

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

$validActions = ['approve', 'reject', 'close'];
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
if ($action === 'approve') {
    $newStatus = 'active';
} elseif ($action === 'reject') {
    $newStatus = 'rejected';
} else {
    $newStatus = 'closed';
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
    $actionKey = $action === 'approve' ? 'event_approved' : ($action === 'reject' ? 'event_rejected' : 'event_closed');
    $details   = $action === 'approve' ? "Approved event ID {$eventId}" : ($action === 'reject' ? "Rejected event ID {$eventId}" : "Closed event ID {$eventId}");
    log_activity($conn, $actorId, $actorRole, $actionKey, 'event', $eventId, $details);

    // Notify organizer when event is approved or rejected
    if (in_array($action, ['approve', 'reject'], true)) {
        $evStmt = $conn->prepare("SELECT e.title, e.organizer_id, e.reject_reason, u.email FROM events e JOIN users u ON e.organizer_id = u.id WHERE e.id = ?");
        $evStmt->bind_param("i", $eventId);
        $evStmt->execute();
        $ev = $evStmt->get_result();
        $evStmt->close();
        if ($ev && $row = $ev->fetch_assoc()) {
            $organizerId = (int)$row['organizer_id'];
            $eventTitle  = $row['title'] ?? 'Event';
            $organizerEmail = $row['email'] ?? '';
            $notifType   = $action === 'approve' ? 'event_approved' : 'event_rejected';
            $notifTitle  = $action === 'approve' ? 'Event approved' : 'Event rejected';
            $notifMsg    = $action === 'approve'
                ? ('Your event "' . $eventTitle . '" has been approved and is now visible to students.')
                : ('Your event "' . $eventTitle . '" was rejected.' . ($row['reject_reason'] ? ' Reason: ' . $row['reject_reason'] : ''));
            $ins = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, ?, ?, ?, ?)");
            if ($ins) {
                $ins->bind_param("isssi", $organizerId, $notifType, $notifTitle, $notifMsg, $eventId);
                $ins->execute();
                $ins->close();
            }
            // Optional: send email to organizer (works if server mail is configured)
            if ($organizerEmail && function_exists('mail')) {
                $subject = '[EVENTIFY] ' . $notifTitle . ': ' . $eventTitle;
                $body    = $notifMsg . "\n\nLog in to your organizer dashboard to view details.";
                $headers = "From: noreply@eventify.local\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                @mail($organizerEmail, $subject, $body, $headers);
            }
        }
    }

    $conn->close();

    $msg = $action === 'approve' ? "Event approved." : ($action === 'reject' ? "Event rejected." : "Event closed.");
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

