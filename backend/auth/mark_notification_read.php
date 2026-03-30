<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';

$role = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['user_id'] ?? 0);

if ($user_id < 1 || !in_array($role, ['organizer', 'student', 'admin', 'super_admin'], true)) {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$redirect = BASE_URL . '/backend/auth/dashboardorganizer.php';
if ($role === 'student') {
    $redirect = BASE_URL . '/backend/auth/dashboard_student.php';
} elseif ($role === 'admin') {
    $redirect = BASE_URL . '/backend/admin/dashboard.php';
} elseif ($role === 'super_admin') {
    $redirect = BASE_URL . '/backend/super_admin/dashboardsuperadmin.php';
}

if (!empty($_GET['clear_all'])) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
} elseif (!empty($_GET['mark_all'])) {
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
