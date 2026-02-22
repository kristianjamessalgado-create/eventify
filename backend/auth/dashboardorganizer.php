<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}


$session_user_id = $_SESSION['user_id'];

// Fetch user info (for profile editing and display)
$user = ['id' => $session_user_id, 'name' => '', 'profile_picture' => null];
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_profile_picture);
$stmt->fetch();
$stmt->close();
$user['name'] = $db_name ?? '';
$user['profile_picture'] = $db_profile_picture;
$user_name = $user['name'] ?: 'Organizer';

// Fetch events
$events = [];
$stmt2 = $conn->prepare("SELECT * FROM events WHERE organizer_id = ?");
$stmt2->bind_param("i", $session_user_id);
$stmt2->execute();
$result = $stmt2->get_result();
if ($result) {
    $events = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt2->close();
$conn->close();


$msg = $_GET['msg'] ?? '';


include __DIR__ . '/../../views/dashboardorganizer.php';
