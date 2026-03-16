<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';

// Only super_admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

// Logged-in super admin name (from session, or fetch from DB)
$superadmin_name = $_SESSION['name'] ?? '';
if ($superadmin_name === '') {
    $stmtUser = $conn->prepare("SELECT name FROM users WHERE id = ? AND role = 'super_admin'");
    $stmtUser->bind_param("i", $_SESSION['user_id']);
    $stmtUser->execute();
    $stmtUser->bind_result($superadmin_name);
    $stmtUser->fetch();
    $stmtUser->close();
    if ($superadmin_name === null || $superadmin_name === '') {
        $superadmin_name = 'Super Admin';
    }
}

// Fetch users
$stmt = $conn->prepare("SELECT id, name, email, role, status FROM users");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch pending events (for modal)
$pendingEvents = [];
$stmtPending = $conn->prepare("
    SELECT e.id, e.title, e.description, e.date, e.start_time, e.end_time, e.location, e.department, e.status,
           u.name AS organizer_name, u.email AS organizer_email
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'pending'
    ORDER BY e.date ASC, e.id ASC
");
if ($stmtPending && $stmtPending->execute()) {
    $resPending = $stmtPending->get_result();
    if ($resPending) {
        $pendingEvents = $resPending->fetch_all(MYSQLI_ASSOC);
    }
    $stmtPending->close();
}

// Fetch ALL events for Event Management and Calendar modals
$allEvents = [];
$resAll = $conn->query("
    SELECT e.id, e.title, e.description, e.date, e.start_time, e.end_time, e.location, e.department, e.status, e.created_at,
           u.name AS organizer_name, u.email AS organizer_email
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    ORDER BY e.date DESC, e.id DESC
");
if ($resAll) {
    while ($row = $resAll->fetch_assoc()) {
        $allEvents[] = $row;
    }
}

// Fetch recent activity logs (latest 20)
$logs = [];
$sqlLogs = "
    SELECT l.id, l.actor_id, l.actor_role, l.action, l.target_type, l.target_id, l.details, l.created_at,
           u.name AS actor_name
    FROM activity_logs l
    LEFT JOIN users u ON l.actor_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 20
";
if ($resLogs = $conn->query($sqlLogs)) {
    while ($row = $resLogs->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Quick stats for dashboard cards

// User counts by role and status
$userStats = [
    'total'        => count($users),
    'super_admin'  => 0,
    'admin'        => 0,
    'organizer'    => 0,
    'multimedia'   => 0,
    'student'      => 0,
    'active'       => 0,
    'inactive'     => 0,
];
foreach ($users as $u) {
    $role = $u['role'] ?? '';
    $status = $u['status'] ?? '';
    if (isset($userStats[$role])) {
        $userStats[$role]++;
    }
    if ($status === 'active') {
        $userStats['active']++;
    } elseif ($status === 'inactive') {
        $userStats['inactive']++;
    }
}

// Event stats
$eventStats = [
    'total'    => 0,
    'pending'  => 0,
    'active'   => 0,
    'rejected' => 0,
    'closed'   => 0,
];
$sqlEvents = "
    SELECT status, COUNT(*) AS cnt
    FROM events
    GROUP BY status
";
if ($resEvents = $conn->query($sqlEvents)) {
    while ($row = $resEvents->fetch_assoc()) {
        $status = $row['status'] ?? '';
        $count  = (int)($row['cnt'] ?? 0);
        $eventStats['total'] += $count;
        if (isset($eventStats[$status])) {
            $eventStats[$status] += $count;
        }
    }
}

// Logins today (from activity_logs)
$loginTodayCount = 0;
$sqlLoginsToday = "
    SELECT COUNT(*) AS total
    FROM activity_logs
    WHERE action = 'login_success'
      AND DATE(created_at) = CURDATE()
";
if ($resLogins = $conn->query($sqlLoginsToday)) {
    if ($row = $resLogins->fetch_assoc()) {
        $loginTodayCount = (int)($row['total'] ?? 0);
    }
}

// We keep connection open in case the view needs it later

// Success message
$success = $_GET['success'] ?? '';

// Load view
define('EVENTIFY_SUPERADMIN_DASHBOARD_LOADED', true);
include __DIR__ . '/../../super_admin/dashboardsuperadmin.php';
