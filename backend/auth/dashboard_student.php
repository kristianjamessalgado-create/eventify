<?php
session_start();

// Include DB, config, and CSRF
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php'; // for BASE_URL
include __DIR__ . '/../../config/csrf.php';

// Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
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

// Include the view
include __DIR__ . '/../../views/dashboard_student.php';
