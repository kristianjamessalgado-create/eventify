<?php
session_start();
if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$msg = 'Invalid event.';

if ($event_id > 0) {
    $stmt = $conn->prepare("SELECT id, status, max_capacity FROM events WHERE id = ?");
    if (!$stmt) {
        $stmt = $conn->prepare("SELECT id, status FROM events WHERE id = ?");
    }
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($ev && !array_key_exists('max_capacity', $ev)) {
        $ev['max_capacity'] = null;
    }

    if (!$ev || ($ev['status'] ?? '') !== 'active') {
        $msg = 'Event not found or not open for registration.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $stmt->close();
            $msg = "You are already registered for this event.";
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
                }
            }
            if ($msg === 'Invalid event.') {
                $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $event_id);
                if ($stmt->execute()) {
                    $msg = "Successfully registered!";
                } else {
                    $msg = "Could not register. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}

header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
