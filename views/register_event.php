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
    $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $stmt->close();
        $msg = "Successfully registered!";
    } else {
        $stmt->close();
        $msg = "You are already registered for this event.";
    }
}

header("Location: " . BASE_URL . "/backend/auth/dashboard_student.php?msg=" . urlencode($msg));
exit();
