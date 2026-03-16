<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'multimedia') {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Access denied"));
    exit();
}

$session_user_id = (int) $_SESSION['user_id'];

// Fetch user info (including department and profile picture)
$stmt = $conn->prepare("SELECT id, user_id, name, department, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

$user_name  = $user['name'] ?? 'Multimedia';
$user_department = $user['department'] ?? null;

// Fetch all events (newest date first)
$events = [];
$uid = (int)$session_user_id;
$res = $conn->query("
    SELECT e.id, e.title, e.date, e.location, e.department,
           (SELECT COUNT(*) FROM event_photos p WHERE p.event_id = e.id) AS photo_count,
           (SELECT COUNT(*) FROM event_photos p WHERE p.event_id = e.id AND p.uploaded_by = {$uid}) AS my_photo_count
    FROM events e
    WHERE e.title NOT LIKE 'sample%'
      AND (" . (empty($user_department) ? "1=1" : "e.department = '" . $conn->real_escape_string($user_department) . "' OR e.department = 'ALL'") . ")
    ORDER BY e.date DESC, e.id DESC
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}

// Fetch upcoming events (department-aware) for modal
$upcomingEvents = [];
$today = date('Y-m-d');
if (!empty($user_department)) {
    $stmtUp = $conn->prepare("SELECT id, title, description, date, location, department FROM events WHERE status = 'active' AND date >= ? AND (department = ? OR department = 'ALL') AND title NOT LIKE 'sample%' ORDER BY date ASC, id ASC LIMIT 12");
    if ($stmtUp) {
        $stmtUp->bind_param("ss", $today, $user_department);
        if ($stmtUp->execute()) {
            $resUp = $stmtUp->get_result();
            if ($resUp) $upcomingEvents = $resUp->fetch_all(MYSQLI_ASSOC);
        }
        $stmtUp->close();
    }
} else {
    $stmtUp = $conn->prepare("SELECT id, title, description, date, location, department FROM events WHERE status = 'active' AND date >= ? AND title NOT LIKE 'sample%' ORDER BY date ASC, id ASC LIMIT 12");
    if ($stmtUp) {
        $stmtUp->bind_param("s", $today);
        if ($stmtUp->execute()) {
            $resUp = $stmtUp->get_result();
            if ($resUp) $upcomingEvents = $resUp->fetch_all(MYSQLI_ASSOC);
        }
        $stmtUp->close();
    }
}

// Fetch photos per event so we can show thumbnails / gallery
$photosByEvent = [];
if (!empty($events)) {
    $ids = array_column($events, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "SELECT id, event_id, file_path FROM event_photos WHERE event_id IN ($placeholders) ORDER BY created_at DESC, id DESC";
    $stmtPhotos = $conn->prepare($sql);
    if ($stmtPhotos) {
        $stmtPhotos->bind_param($types, ...$ids);
        $stmtPhotos->execute();
        $resultPhotos = $stmtPhotos->get_result();
        while ($row = $resultPhotos->fetch_assoc()) {
            $eid = (int)$row['event_id'];
            if (!isset($photosByEvent[$eid])) {
                $photosByEvent[$eid] = [];
            }
            // Store all photos (id + file path) for this event
            $photosByEvent[$eid][] = [
                'id' => (int)$row['id'],
                'file_path' => $row['file_path'],
            ];
        }
        $stmtPhotos->close();
    }
}

$msg = $_GET['msg'] ?? '';
$conn->close();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

include __DIR__ . '/../../views/dashboard_multimedia.php';
