<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$redirect = BASE_URL . '/backend/auth/dashboardorganizer.php';

if (!empty($_GET['mark_all'])) {
    $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: " . $redirect);
exit();
