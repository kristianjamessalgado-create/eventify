<?php
session_start();
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/event_status_auto.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

eventify_auto_complete_past_events($conn);

$hasMustChangePasswordColumn = false;
try {
    $cpCol = $conn->query("SHOW COLUMNS FROM users LIKE 'must_change_password'");
    $hasMustChangePasswordColumn = (bool)($cpCol && $cpCol->num_rows > 0);
} catch (Throwable $e) {
    $hasMustChangePasswordColumn = false;
}
if ($hasMustChangePasswordColumn) {
    $forceCp = $conn->prepare("SELECT must_change_password FROM users WHERE id = ? LIMIT 1");
    if ($forceCp) {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $forceCp->bind_param("i", $uid);
        $forceCp->execute();
        $cpRow = $forceCp->get_result()->fetch_assoc();
        $forceCp->close();
        if ((int)($cpRow['must_change_password'] ?? 0) === 1) {
            header("Location: " . BASE_URL . "/views/change_password.php?from=required&next=" . urlencode(BASE_URL . "/backend/auth/dashboardorganizer.php"));
            exit();
        }
    }
}


$session_user_id = $_SESSION['user_id'];

// Fetch user info (for profile editing and display)
$user = ['id' => $session_user_id, 'name' => '', 'profile_picture' => null, 'email' => '', 'organizer_contact_email' => '', 'organizer_phone' => '', 'organizer_contact_method' => 'email'];
$userHasContactColumns = false;
try {
    $colCheck = $conn->query("SHOW COLUMNS FROM users WHERE Field IN ('organizer_contact_email','organizer_phone','organizer_contact_method')");
    $userHasContactColumns = (bool) ($colCheck && $colCheck->num_rows >= 3);
} catch (Throwable $e) {
    $userHasContactColumns = false;
}

if ($userHasContactColumns) {
    $stmt = $conn->prepare("SELECT name, profile_picture, email, organizer_contact_email, organizer_phone, organizer_contact_method FROM users WHERE id = ?");
    $stmt->bind_param("i", $session_user_id);
    $stmt->execute();
    $stmt->bind_result($db_name, $db_profile_picture, $db_email, $db_contact_email, $db_phone, $db_contact_method);
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT name, profile_picture, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $session_user_id);
    $stmt->execute();
    $stmt->bind_result($db_name, $db_profile_picture, $db_email);
    $stmt->fetch();
    $stmt->close();
    $db_contact_email = '';
    $db_phone = '';
    $db_contact_method = 'email';
}
$user['name'] = $db_name ?? '';
$user['profile_picture'] = $db_profile_picture;
$user['email'] = $db_email ?? '';
$user['organizer_contact_email'] = $db_contact_email ?? '';
$user['organizer_phone'] = $db_phone ?? '';
$user['organizer_contact_method'] = in_array($db_contact_method, ['email', 'phone'], true) ? $db_contact_method : 'email';
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

// Feedback analytics (requires event_feedback table)
$feedbackStats = [
    'total_feedback' => 0,
    'avg_rating' => 0.0,
    'five_star' => 0,
];
try {
    $fStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_feedback,
            AVG(ef.rating) AS avg_rating,
            SUM(CASE WHEN ef.rating = 5 THEN 1 ELSE 0 END) AS five_star
        FROM event_feedback ef
        JOIN events e ON e.id = ef.event_id
        WHERE e.organizer_id = ?
    ");
    if ($fStmt) {
        $fStmt->bind_param("i", $session_user_id);
        $fStmt->execute();
        $row = $fStmt->get_result()->fetch_assoc();
        $fStmt->close();
        if ($row) {
            $feedbackStats['total_feedback'] = (int) ($row['total_feedback'] ?? 0);
            $feedbackStats['avg_rating'] = (float) ($row['avg_rating'] ?? 0);
            $feedbackStats['five_star'] = (int) ($row['five_star'] ?? 0);
        }
    }
} catch (Throwable $e) {
    $feedbackStats = ['total_feedback' => 0, 'avg_rating' => 0.0, 'five_star' => 0];
}

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

$eventsHasGeo = false;
try {
    $geoColCheck = $conn->query("SHOW COLUMNS FROM events WHERE Field IN ('latitude','longitude')");
    if ($geoColCheck && $geoColCheck->num_rows >= 2) {
        $eventsHasGeo = true;
    }
} catch (Throwable $e) {
    $eventsHasGeo = false;
}

$conn->close();


$msg = $_GET['msg'] ?? '';


include __DIR__ . '/../../views/dashboardorganizer.php';
