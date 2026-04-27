<?php
/**
 * Student RSVP (POST + CSRF). Respects max_capacity when column exists.
 */
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../config/departments.php';

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
$msg      = 'Invalid event.';

$eventsHasMaxCapacity = false;
try {
    $mcCol = $conn->query("SHOW COLUMNS FROM events WHERE Field = 'max_capacity'");
    if ($mcCol && $mcCol->num_rows >= 1) {
        $eventsHasMaxCapacity = true;
    }
} catch (Throwable $e) {
    $eventsHasMaxCapacity = false;
}

if ($event_id > 0) {
    if ($eventsHasMaxCapacity) {
        $stmt = $conn->prepare("SELECT e.id, e.status, e.max_capacity, e.department, u.department AS student_department FROM events e JOIN users u ON u.id = ? WHERE e.id = ?");
    } else {
        $stmt = $conn->prepare("SELECT e.id, e.status, e.department, u.department AS student_department FROM events e JOIN users u ON u.id = ? WHERE e.id = ?");
    }
    if (!$stmt) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode("Server error."));
        exit();
    }
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($ev && !array_key_exists('max_capacity', $ev)) {
        $ev['max_capacity'] = null;
    }

    if (!$ev || ($ev['status'] ?? '') !== 'active') {
        $msg = 'Event not found or not open for registration.';
    } elseif (!eventify_student_sees_event_department((string)($ev['department'] ?? 'ALL'), $ev['student_department'] ?? null)) {
        $msg = 'This event is not available for your department.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $stmt->close();
            $msg = 'You are already registered for this event.';
        } else {
            $stmt->close();
            $maxCap = isset($ev['max_capacity']) && $ev['max_capacity'] !== null && $ev['max_capacity'] !== ''
                ? (int) $ev['max_capacity']
                : null;
            if ($maxCap !== null && $maxCap > 0) {
                $cStmt = $conn->prepare("SELECT COUNT(*) AS c FROM registrations WHERE event_id = ?");
                $cStmt->bind_param("i", $event_id);
                $cStmt->execute();
                $cStmt->bind_result($cnt);
                $cStmt->fetch();
                $cStmt->close();
                if ((int) $cnt >= $maxCap) {
                    $msg = 'This event is full. No more seats available.';
                    header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
                    exit();
                }
            }
            $ins = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            $ins->bind_param("ii", $user_id, $event_id);
            if ($ins->execute()) {
                $msg = 'Successfully registered!';
                // Student notification copy (for in-app history)
                try {
                    $evTitle = '';
                    $tStmt = $conn->prepare("SELECT title FROM events WHERE id = ? LIMIT 1");
                    if ($tStmt) {
                        $tStmt->bind_param("i", $event_id);
                        $tStmt->execute();
                        $tStmt->bind_result($evTitle);
                        $tStmt->fetch();
                        $tStmt->close();
                    }
                    $nt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, event_id) VALUES (?, 'rsvp_confirmed', 'RSVP confirmed', ?, ?)");
                    if ($nt) {
                        $nMsg = 'You are registered for "' . ($evTitle ?: 'this event') . '".';
                        $nt->bind_param("isi", $user_id, $nMsg, $event_id);
                        $nt->execute();
                        $nt->close();
                    }
                } catch (Throwable $e) {
                    // ignore if notifications table is unavailable
                }
            } else {
                $msg = 'Could not register. Please try again.';
            }
            $ins->close();
        }
    }
}

$conn->close();
header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
