<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'multimedia') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid request."));
    exit();
}

$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$user_id = (int) $_SESSION['user_id'];
if ($event_id < 1) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Invalid event."));
    exit();
}

$checkCol = $conn->query("SHOW COLUMNS FROM event_photos LIKE 'status'");
if (!$checkCol || $checkCol->num_rows < 1) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode("Publishing workflow requires DB migration."));
    exit();
}

$stmt = $conn->prepare("UPDATE event_photos SET status = 'published', published_at = NOW() WHERE event_id = ? AND uploaded_by = ? AND status <> 'published'");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();
$conn->close();

$msg = $updated > 0 ? ($updated . " photo(s) published.") : "No draft photos to publish for this event.";
header("Location: " . BASE_URL . "/backend/auth/dashboard_multimedia.php?msg=" . urlencode($msg));
exit();
