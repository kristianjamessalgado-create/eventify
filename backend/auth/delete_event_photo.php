<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'multimedia') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['photo_id']) || empty($_POST['event_id'])) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid delete request."));
    exit();
}

$photo_id = (int) $_POST['photo_id'];
$event_id = (int) $_POST['event_id'];

// Find photo and ensure it belongs to this multimedia user
$stmt = $conn->prepare("SELECT file_path, uploaded_by FROM event_photos WHERE id = ? AND event_id = ?");
$stmt->bind_param("ii", $photo_id, $event_id);
$stmt->execute();
$stmt->bind_result($file_path, $uploaded_by);
if (!$stmt->fetch()) {
    $stmt->close();
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Photo not found."));
    exit();
}
$stmt->close();

if ((int)$uploaded_by !== $user_id) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("You can only delete your own photos."));
    exit();
}

// Delete file on disk
$base_dir = dirname(__DIR__, 2); // project root
$full_path = $base_dir . '/' . $file_path;
if (is_file($full_path)) {
    @unlink($full_path);
}

// Delete DB row
$del = $conn->prepare("DELETE FROM event_photos WHERE id = ?");
$del->bind_param("i", $photo_id);
$del->execute();
$del->close();
$conn->close();

header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Photo deleted."));
exit();

