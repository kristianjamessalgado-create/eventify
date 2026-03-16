<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

// Only admin users can access this dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

$session_user_id = (int) $_SESSION['user_id'];

// Fetch admin info (for header/profile)
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email);
$stmt->fetch();
$stmt->close();

$admin_name  = $db_name ?: 'Admin';
$admin_email = $db_email ?: '';

// Fetch all events for calendar (only approved + pending for visibility)
$events = [];
$result = $conn->query("
    SELECT e.id, e.title, e.description, e.date, e.start_time, e.end_time, e.location, e.department,
           e.created_at, e.status,
           u.name AS organizer_name
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    ORDER BY e.date ASC, e.id ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Fetch count of pending events and pending list for modal
$pendingCount = 0;
$pendingEvents = [];
$stmtPending = $conn->prepare("
    SELECT e.id, e.title, e.description, e.date, e.location, e.department, e.status,
           u.name AS organizer_name, u.email AS organizer_email
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'pending'
    ORDER BY e.date ASC, e.id ASC
");
if ($stmtPending && $stmtPending->execute()) {
    $resP = $stmtPending->get_result();
    if ($resP) {
        $pendingEvents = $resP->fetch_all(MYSQLI_ASSOC);
        $pendingCount = count($pendingEvents);
    }
    $stmtPending->close();
}

// Event stats for dashboard cards
$eventStats = ['total' => 0, 'pending' => 0, 'active' => 0, 'rejected' => 0, 'closed' => 0];
$resStats = $conn->query("SELECT status, COUNT(*) AS cnt FROM events GROUP BY status");
if ($resStats) {
    while ($row = $resStats->fetch_assoc()) {
        $eventStats['total'] += (int) $row['cnt'];
        if (isset($eventStats[$row['status']])) {
            $eventStats[$row['status']] = (int) $row['cnt'];
        }
    }
}

$conn->close();

define('EVENTIFY_ADMIN_DASHBOARD_LOADED', true);
include __DIR__ . '/../../admin/dashboard.php';

