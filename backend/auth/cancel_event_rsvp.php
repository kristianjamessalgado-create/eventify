<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Invalid request."));
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;

if ($event_id < 1) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Invalid event."));
    exit();
}

$eStmt = $conn->prepare("SELECT id, title, date FROM events WHERE id = ? LIMIT 1");
$eStmt->bind_param("i", $event_id);
$eStmt->execute();
$event = $eStmt->get_result()->fetch_assoc();
$eStmt->close();

if (!$event) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Event not found."));
    exit();
}

if (($event['date'] ?? '') < date('Y-m-d')) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Past events can no longer be cancelled."));
    exit();
}

$dStmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1");
$dStmt->bind_param("ii", $user_id, $event_id);
$dStmt->execute();
$deleted = $dStmt->affected_rows > 0;
$dStmt->close();

if ($deleted) {
    try {
        $title = (string) ($event['title'] ?? 'this event');
        $n = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'rsvp_cancelled', 'RSVP cancelled', ?, ?)");
        if ($n) {
            $nMsg = 'You cancelled your RSVP for "' . $title . '".';
            $n->bind_param("isi", $user_id, $nMsg, $event_id);
            $n->execute();
            $n->close();
        }
    } catch (Throwable $e) {
        // ignore notifications failures
    }
}
$msg = $deleted ? "RSVP cancelled successfully." : "You are not registered for this event.";
$conn->close();
header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
