<?php
session_start();

// Include DB, config, and CSRF
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php'; // for BASE_URL
include __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/event_status_auto.php';

// Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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
            header("Location: " . BASE_URL . "/views/change_password.php?from=required&next=" . urlencode(BASE_URL . "/backend/auth/dashboard_student.php"));
            exit();
        }
    }
}

// Logged-in user's ID
$session_user_id = $_SESSION['user_id'];

// Fetch user info (including department and profile picture)
$stmt = $conn->prepare("SELECT id, user_id, name, department, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Safe defaults
$user_name  = $user['name'] ?? 'Student';
$department = $user['department'] ?? null;
$events     = [];
$msg        = $_GET['msg'] ?? '';
$error      = $_GET['error'] ?? '';

// Student settings (lazy migration + defaults for backward compatibility)
$studentSettings = [
    'event_reminders' => 1,
    'rsvp_updates' => 1,
    'announcement_notifications' => 1,
    'notif_channel_email' => 1,
    'default_calendar_view' => 'dayGridMonth',
    'show_calendar_legend' => 1,
    'auto_add_rsvp_calendar' => 1,
    'reminder_timing' => '1_day',
    'hide_past_rsvped' => 0,
    'share_profile_with_organizers' => 1,
    'allow_photo_tagging' => 1,
];
try {
    $conn->query("
        CREATE TABLE IF NOT EXISTS student_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            event_reminders TINYINT(1) NOT NULL DEFAULT 1,
            rsvp_updates TINYINT(1) NOT NULL DEFAULT 1,
            announcement_notifications TINYINT(1) NOT NULL DEFAULT 1,
            notif_channel_email TINYINT(1) NOT NULL DEFAULT 1,
            default_calendar_view VARCHAR(20) NOT NULL DEFAULT 'dayGridMonth',
            show_calendar_legend TINYINT(1) NOT NULL DEFAULT 1,
            auto_add_rsvp_calendar TINYINT(1) NOT NULL DEFAULT 1,
            reminder_timing VARCHAR(20) NOT NULL DEFAULT '1_day',
            hide_past_rsvped TINYINT(1) NOT NULL DEFAULT 0,
            share_profile_with_organizers TINYINT(1) NOT NULL DEFAULT 1,
            allow_photo_tagging TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_student_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $stmtSettings = $conn->prepare("
        SELECT event_reminders, rsvp_updates, announcement_notifications, notif_channel_email,
               default_calendar_view, show_calendar_legend, auto_add_rsvp_calendar, reminder_timing,
               hide_past_rsvped, share_profile_with_organizers, allow_photo_tagging
        FROM student_settings
        WHERE user_id = ?
        LIMIT 1
    ");
    if ($stmtSettings) {
        $stmtSettings->bind_param('i', $session_user_id);
        if ($stmtSettings->execute()) {
            $resSettings = $stmtSettings->get_result();
            if ($resSettings && ($settingsRow = $resSettings->fetch_assoc())) {
                $studentSettings = array_merge($studentSettings, $settingsRow);
            }
        }
        $stmtSettings->close();
    }
} catch (Throwable $e) {
    // Keep dashboard available when settings table is unavailable.
}

// Fetch events filtered by student's department
if ($department) {
    $stmt2 = $conn->prepare("SELECT * FROM events WHERE status = 'active' AND (department = ? OR department = 'ALL') ORDER BY date ASC");
    $stmt2->bind_param("s", $department);
} else {
    // Fallback: if no department set, show all active events
    $stmt2 = $conn->prepare("SELECT * FROM events WHERE status = 'active' ORDER BY date ASC");
}

if ($stmt2 && $stmt2->execute()) {
    $result2 = $stmt2->get_result();
    if ($result2) {
        $events = $result2->fetch_all(MYSQLI_ASSOC);
    }
    $stmt2->close();
}

// Fetch this student's attendance records (events they checked into via QR)
$attendance_records = [];
$stmt_att = $conn->prepare("
    SELECT r.id, r.event_id, r.status, r.time_in, r.time_out,
           e.title AS event_title, e.date AS event_date, e.location AS event_location
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ? AND r.status = 'present' AND r.time_in IS NOT NULL
    ORDER BY r.time_in DESC
");
$stmt_att->bind_param("i", $session_user_id);
if ($stmt_att->execute()) {
    $res_att = $stmt_att->get_result();
    if ($res_att) {
        $attendance_records = $res_att->fetch_all(MYSQLI_ASSOC);
    }
    $stmt_att->close();
}

// RSVP: which events this student is registered for
$registered_event_ids = [];
$stmtReg = $conn->prepare("SELECT event_id FROM registrations WHERE user_id = ?");
$stmtReg->bind_param("i", $session_user_id);
if ($stmtReg->execute()) {
    $rr = $stmtReg->get_result();
    if ($rr) {
        while ($row = $rr->fetch_assoc()) {
            $registered_event_ids[] = (int) $row['event_id'];
        }
    }
    $stmtReg->close();
}

// RSVP counts per event (for capacity display)
$reg_count_by_event = [];
$rc = $conn->query("SELECT event_id, COUNT(*) AS cnt FROM registrations GROUP BY event_id");
if ($rc) {
    while ($row = $rc->fetch_assoc()) {
        $reg_count_by_event[(int) $row['event_id']] = (int) $row['cnt'];
    }
}

// Feedback already submitted (event_feedback table may not exist yet)
$feedback_submitted_ids = [];
try {
    $stmtFb = $conn->prepare("SELECT event_id FROM event_feedback WHERE user_id = ?");
    if ($stmtFb) {
        $stmtFb->bind_param("i", $session_user_id);
        if ($stmtFb->execute()) {
            $rf = $stmtFb->get_result();
            if ($rf) {
                while ($row = $rf->fetch_assoc()) {
                    $feedback_submitted_ids[] = (int) $row['event_id'];
                }
            }
        }
        $stmtFb->close();
    }
} catch (Throwable $e) {
    $feedback_submitted_ids = [];
}

// In-app notifications
$student_notifications = [];
$unread_notif_count = 0;
try {
    $stmtN = $conn->prepare("SELECT id, type, title, message, event_id, read_at, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 40");
    if ($stmtN) {
        $stmtN->bind_param("i", $session_user_id);
        if ($stmtN->execute()) {
            $rn = $stmtN->get_result();
            if ($rn) {
                $student_notifications = $rn->fetch_all(MYSQLI_ASSOC);
            }
        }
        $stmtN->close();
    }
    foreach ($student_notifications as $n) {
        if (empty($n['read_at'])) {
            $unread_notif_count++;
        }
    }
} catch (Throwable $e) {
    $student_notifications = [];
    $unread_notif_count = 0;
}

// Include the view
include __DIR__ . '/../../views/dashboard_student.php';
