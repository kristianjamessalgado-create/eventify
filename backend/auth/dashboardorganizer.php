<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';


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

// Fetch events for this organizer
$events = [];
$stmt2 = $conn->prepare("SELECT * FROM events WHERE organizer_id = ? ORDER BY date ASC, id ASC");
$stmt2->bind_param("i", $session_user_id);
$stmt2->execute();
$result = $stmt2->get_result();
if ($result) {
    $events = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt2->close();

// Quick stats for organizer dashboard
$today = date('Y-m-d');
$upcomingCount = 0;
$pendingCount = 0;
$thisWeekCount = 0;
$rejectedCount = 0;

foreach ($events as $e) {
    $date = $e['date'] ?? null;
    $status = strtolower($e['status'] ?? '');
    if ($date && $date >= $today) {
        $upcomingCount++;
    }
    if ($status === 'pending') {
        $pendingCount++;
    } elseif ($status === 'rejected') {
        $rejectedCount++;
    }

    if ($date && $date >= $today) {
        $diffDays = (strtotime($date) - strtotime($today)) / 86400;
        if ($diffDays >= 0 && $diffDays <= 7) {
            $thisWeekCount++;
        }
    }
}

$organizerStats = [
    'upcoming' => $upcomingCount,
    'pending'  => $pendingCount,
    'thisWeek' => $thisWeekCount,
    'rejected' => $rejectedCount,
];

// Fetch unread notifications for organizer (approve/reject etc.)
$organizer_notifications = [];
try {
    $notifStmt = $conn->prepare("SELECT id, type, title, message, event_id, created_at FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC LIMIT 20");
    if ($notifStmt) {
        $notifStmt->bind_param("i", $session_user_id);
        $notifStmt->execute();
        $notifRes = $notifStmt->get_result();
        if ($notifRes) {
            $organizer_notifications = $notifRes->fetch_all(MYSQLI_ASSOC);
        }
        $notifStmt->close();
    }
} catch (mysqli_sql_exception $e) {
    // Table may not exist yet; use empty list so dashboard still loads
    $organizer_notifications = [];
}

$conn->close();


$msg = $_GET['msg'] ?? '';


include __DIR__ . '/../../views/dashboardorganizer.php';
