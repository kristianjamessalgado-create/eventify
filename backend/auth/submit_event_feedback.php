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
    $stmt = $conn->prepare("SELECT id, date, status FROM events WHERE id = ? AND status IN ('active','completed','closed')");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$ev) {
        $msg = 'Event not found.';
    } else {
        $today = date('Y-m-d');
        $evDate = (string)($ev['date'] ?? '');
        $st = strtolower((string)($ev['status'] ?? ''));
        $organizerEnded = ($st === 'closed' || $st === 'completed');
        $pastByDate = ($evDate !== '' && $evDate < $today);
        if (!$pastByDate && !$organizerEnded) {
            $msg = 'Feedback is only available after the event date, or once the organizer marks the event as ended.';
        } else {
            // Only students with recorded attendance (check-in) may submit feedback; stored with user_id for one-per-student, shown anonymously to organizers.
            $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'present' AND time_in IS NOT NULL LIMIT 1");
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $stmt->close();
                $msg = 'Only students who attended (QR check-in) can leave feedback for this event.';
            } else {
                $stmt->close();

                $chk = $conn->prepare("SELECT id FROM event_feedback WHERE event_id = ? AND user_id = ? LIMIT 1");
                if ($chk) {
                    $chk->bind_param("ii", $event_id, $user_id);
                    $chk->execute();
                    $chkRes = $chk->get_result();
                    $already = (bool)($chkRes && $chkRes->num_rows > 0);
                    $chk->close();
                } else {
                    $already = false;
                }

                if ($already) {
                    $msg = 'You already submitted feedback for this event.';
                    $_SESSION['eventify_feedback_ack'] = $_SESSION['eventify_feedback_ack'] ?? [];
                    if (!in_array($event_id, $_SESSION['eventify_feedback_ack'], true)) {
                        $_SESSION['eventify_feedback_ack'][] = $event_id;
                    }
                } else {
                    $ins = $conn->prepare("INSERT INTO event_feedback (event_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $ins->bind_param("iiis", $event_id, $user_id, $rating, $comment);
                    if ($ins->execute()) {
                        $msg = 'Thank you — your anonymous feedback was saved.';
                        $_SESSION['eventify_feedback_ack'] = $_SESSION['eventify_feedback_ack'] ?? [];
                        if (!in_array($event_id, $_SESSION['eventify_feedback_ack'], true)) {
                            $_SESSION['eventify_feedback_ack'][] = $event_id;
                        }
                    } else {
                        $msg = 'Could not save feedback right now. Please try again.';
                    }
                    $ins->close();
                }
            }
        }
    }
} catch (Throwable $e) {
    $msg = 'Feedback is not available yet. Ask admin to run the database migration for event_feedback.';
}

$conn->close();
header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
