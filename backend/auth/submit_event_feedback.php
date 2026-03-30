<?php
/**
 * Student post-event feedback (rating + optional comment).
 */
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

$user_id  = (int) $_SESSION['user_id'];
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$rating   = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$comment  = trim($_POST['comment'] ?? '');
$msg      = 'Invalid feedback.';

if ($event_id < 1 || $rating < 1 || $rating > 5) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
    exit();
}

if (strlen($comment) > 2000) {
    $comment = mb_substr($comment, 0, 2000);
}

try {
    $stmt = $conn->prepare("SELECT id, date, status FROM events WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$ev) {
        $msg = 'Event not found.';
    } elseif (($ev['date'] ?? '') >= date('Y-m-d')) {
        $msg = 'Feedback is only available after the event date.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $stmt->close();
            $msg = 'You must have registered for this event to leave feedback.';
        } else {
            $stmt->close();
            $ins = $conn->prepare("INSERT INTO event_feedback (event_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $ins->bind_param("iiis", $event_id, $user_id, $rating, $comment);
            if ($ins->execute()) {
                $msg = 'Thank you for your feedback!';
            } else {
                $msg = 'You already submitted feedback for this event.';
            }
            $ins->close();
        }
    }
} catch (Throwable $e) {
    $msg = 'Feedback is not available yet. Ask admin to run the database migration for event_feedback.';
}

$conn->close();
header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
