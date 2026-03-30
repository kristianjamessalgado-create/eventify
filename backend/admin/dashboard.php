<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/event_status_auto.php';

// Only admin users can access this dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
            header("Location: " . BASE_URL . "/views/change_password.php?from=required&next=" . urlencode(BASE_URL . "/backend/admin/dashboard.php"));
            exit();
        }
    }
}

$session_user_id = (int) $_SESSION['user_id'];
$usersHasOtpContactColumns = false;
$otpTableReady = false;
try {
    $colCheck = $conn->query("SHOW COLUMNS FROM users WHERE Field IN ('organizer_contact_email','organizer_phone','organizer_contact_method')");
    $usersHasOtpContactColumns = (bool) ($colCheck && $colCheck->num_rows >= 3);
} catch (Throwable $e) {
    $usersHasOtpContactColumns = false;
}
try {
    $otpTbl = $conn->query("SHOW TABLES LIKE 'event_approval_otps'");
    $otpTableReady = (bool) ($otpTbl && $otpTbl->num_rows > 0);
} catch (Throwable $e) {
    $otpTableReady = false;
}

// Fetch admin info (for header/profile)
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email);
$stmt->fetch();
$stmt->close();

$admin_name  = $db_name ?: 'Admin';
$admin_email = $db_email ?: '';

// Admin in-app notifications
$admin_notifications = [];
$admin_unread_count = 0;
try {
    $stmtN = $conn->prepare("SELECT id, type, title, message, event_id, read_at, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 40");
    if ($stmtN) {
        $stmtN->bind_param("i", $session_user_id);
        if ($stmtN->execute()) {
            $r = $stmtN->get_result();
            if ($r) {
                $admin_notifications = $r->fetch_all(MYSQLI_ASSOC);
            }
        }
        $stmtN->close();
    }
    foreach ($admin_notifications as $n) {
        if (empty($n['read_at'])) {
            $admin_unread_count++;
        }
    }
} catch (Throwable $e) {
    $admin_notifications = [];
    $admin_unread_count = 0;
}

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
$pendingSql = "
    SELECT e.id, e.title, e.description, e.date, e.location, e.department, e.status,
           u.name AS organizer_name, u.email AS organizer_email";
if ($usersHasOtpContactColumns) {
    $pendingSql .= ", u.organizer_contact_email, u.organizer_phone, u.organizer_contact_method";
}
$pendingSql .= "
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'pending'
    ORDER BY e.date ASC, e.id ASC
";
$stmtPending = $conn->prepare($pendingSql);
if ($stmtPending && $stmtPending->execute()) {
    $resP = $stmtPending->get_result();
    if ($resP) {
        $pendingEvents = $resP->fetch_all(MYSQLI_ASSOC);
        $pendingCount = count($pendingEvents);
    }
    $stmtPending->close();
}

// Upcoming events (active + pending, today onward) — shown in sidebar modal on demand only
$todayAdmin = date('Y-m-d');
$upcomingAdminEvents = [];
$stmtUp = $conn->prepare("
    SELECT e.id, e.title, e.description, e.date, e.start_time, e.end_time, e.location, e.department, e.status,
           u.name AS organizer_name
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.date >= ? AND e.status IN ('active', 'pending')
    ORDER BY e.date ASC, e.start_time ASC, e.id ASC
");
if ($stmtUp) {
    $stmtUp->bind_param('s', $todayAdmin);
    if ($stmtUp->execute()) {
        $ru = $stmtUp->get_result();
        if ($ru) {
            $upcomingAdminEvents = $ru->fetch_all(MYSQLI_ASSOC);
        }
    }
    $stmtUp->close();
}
$upcomingAdminCount = count($upcomingAdminEvents);

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

// Chart data: events by department
$chartDeptLabels = [];
$chartDeptCounts = [];
$resDept = $conn->query("SELECT COALESCE(NULLIF(TRIM(department), ''), 'ALL') AS dept, COUNT(*) AS cnt FROM events GROUP BY dept ORDER BY cnt DESC, dept ASC");
if ($resDept) {
    while ($row = $resDept->fetch_assoc()) {
        $d = $row['dept'] ?? 'ALL';
        $chartDeptLabels[] = $d === 'ALL' ? 'All departments' : $d;
        $chartDeptCounts[] = (int) $row['cnt'];
    }
}

// Chart data: events by status (dynamic; supports legacy/custom status values)
$chartStatusLabels = [];
$chartStatusCounts = [];
$resStatusChart = $conn->query("SELECT COALESCE(NULLIF(TRIM(status), ''), 'unknown') AS s, COUNT(*) AS cnt FROM events GROUP BY s ORDER BY cnt DESC, s ASC");
if ($resStatusChart) {
    while ($row = $resStatusChart->fetch_assoc()) {
        $raw = (string) ($row['s'] ?? 'unknown');
        $label = ucfirst($raw);
        if (strtolower($raw) === 'all') {
            $label = 'All';
        }
        $chartStatusLabels[] = $label;
        $chartStatusCounts[] = (int) $row['cnt'];
    }
}

// Feedback analytics (requires event_feedback table)
$feedbackStats = [
    'total_feedback' => 0,
    'avg_rating' => 0.0,
    'rating_labels' => ['1★', '2★', '3★', '4★', '5★'],
    'rating_counts' => [0, 0, 0, 0, 0],
];
try {
    $resFb = $conn->query("SELECT COUNT(*) AS total_feedback, AVG(rating) AS avg_rating FROM event_feedback");
    if ($resFb && ($row = $resFb->fetch_assoc())) {
        $feedbackStats['total_feedback'] = (int) ($row['total_feedback'] ?? 0);
        $feedbackStats['avg_rating'] = (float) ($row['avg_rating'] ?? 0);
    }
    $resFbDist = $conn->query("SELECT rating, COUNT(*) AS cnt FROM event_feedback GROUP BY rating ORDER BY rating ASC");
    if ($resFbDist) {
        while ($r = $resFbDist->fetch_assoc()) {
            $rating = (int) ($r['rating'] ?? 0);
            if ($rating >= 1 && $rating <= 5) {
                $feedbackStats['rating_counts'][$rating - 1] = (int) ($r['cnt'] ?? 0);
            }
        }
    }
} catch (Throwable $e) {
    // Keep dashboard available even when event_feedback is not migrated.
}

// Recent audit log entries (shown in dashboard modal)
$auditLogs = [];
$resAudit = $conn->query("
    SELECT l.id, l.actor_id, l.actor_role, l.action, l.target_type, l.target_id, l.details, l.created_at,
           u.name AS actor_name
    FROM activity_logs l
    LEFT JOIN users u ON l.actor_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 200
");
if ($resAudit) {
    while ($row = $resAudit->fetch_assoc()) {
        $auditLogs[] = $row;
    }
}

$conn->close();

define('EVENTIFY_ADMIN_DASHBOARD_LOADED', true);
include __DIR__ . '/../../admin/dashboard.php';

