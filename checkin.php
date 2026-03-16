<?php
session_start();
if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}
include __DIR__ . '/config/db.php';
include __DIR__ . '/config/config.php';
include __DIR__ . '/config/csrf.php';

$token = trim($_GET['t'] ?? '');
$confirmed = false;
$already_done = false; // Student already confirmed attendance for this event
$error = '';
$event = null;

if ($token === '') {
    $error = 'Invalid or missing check-in link. Please scan the event QR code again.';
} else {
    // Load event by check-in token; ensure token exists (generate for old events)
    $stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, status, checkin_token FROM events WHERE checkin_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $event = $res->fetch_assoc();
    $stmt->close();

    if (!$event) {
        $error = 'This check-in link is invalid or has expired.';
    } elseif (!in_array(strtolower($event['status'] ?? ''), ['active'], true)) {
        $error = 'Check-in is only available for approved events.';
    }
}

// Require student login to confirm attendance
if (!$error && $event && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $returnUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . BASE_URL . '/checkin.php?t=' . urlencode($token);
        header('Location: ' . BASE_URL . '/views/login.php?redirect=' . urlencode($returnUrl));
        exit();
    }
    if ($_SESSION['role'] !== 'student') {
        $error = 'Only students can confirm attendance. Please log in with a student account.';
        $event = null;
    } else {
        // Check if this student already confirmed attendance for this event
        $uid = (int) $_SESSION['user_id'];
        $eid = (int) $event['id'];
        $chk = $conn->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'present' LIMIT 1");
        $chk->bind_param("ii", $uid, $eid);
        $chk->execute();
        $chk->store_result();
        $already_done = $chk->num_rows > 0;
        $chk->close();
    }
}

// Handle confirm attendance (POST)
if (!$error && $event && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
    if (!csrf_validate()) {
        $error = 'Invalid request. Please try again.';
    } else {
    $user_id = (int) $_SESSION['user_id'];
    $event_id = (int) $event['id'];
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status, time_in) VALUES (?, ?, 'present', NOW()) ON DUPLICATE KEY UPDATE status = 'present', time_in = NOW()");
    $stmt->bind_param("ii", $user_id, $event_id);
    if ($stmt->execute()) {
        $confirmed = true;
    } else {
        $error = 'Could not record attendance. Please try again.';
    }
    $stmt->close();
    }
}

$conn->close();

$pageTitle = $event ? htmlspecialchars($event['title']) : 'Event Check-in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Check-in | EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem; }
        .checkin-card { max-width: 420px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
        .checkin-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 1.5rem; border-radius: 16px 16px 0 0; text-align: center; }
        .checkin-body { padding: 1.5rem; background: #fff; border-radius: 0 0 16px 16px; }
        .event-meta { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .btn-confirm { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 10px; }
        .btn-confirm:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body>
    <div class="checkin-card card border-0 overflow-hidden">
        <div class="checkin-header">
            <h1 class="h4 mb-0"><i class="fas fa-qrcode me-2"></i>EVENTIFY Check-in</h1>
        </div>
        <div class="checkin-body">
            <?php if ($error): ?>
                <p class="text-danger mb-0"><?= htmlspecialchars($error) ?></p>
                <a href="<?= BASE_URL ?>" class="btn btn-outline-primary mt-3" target="_top">Go to home</a>
            <?php elseif ($confirmed): ?>
                <p class="text-success mb-2"><i class="fas fa-check-circle me-2"></i><strong>Attendance confirmed.</strong></p>
                <p class="text-muted small mb-0">You have been marked present for this event.</p>
                <a href="<?= BASE_URL ?>/backend/auth/dashboard_student.php" class="btn btn-primary mt-3" target="_top">Back to dashboard</a>
            <?php elseif ($already_done): ?>
                <p class="text-success mb-2"><i class="fas fa-check-circle me-2"></i><strong>You're done with attendance already.</strong></p>
                <p class="text-muted small mb-0">You have already confirmed your attendance for this event. No need to check in again.</p>
                <a href="<?= BASE_URL ?>/backend/auth/dashboard_student.php" class="btn btn-primary mt-3" target="_top">Back to dashboard</a>
            <?php elseif ($event): ?>
                <h5 class="mb-3"><?= htmlspecialchars($event['title']) ?></h5>
                <div class="event-meta">
                    <?php if (!empty($event['date'])): ?>
                        <div><i class="fas fa-calendar-day me-2"></i><?= date('l, M j, Y', strtotime($event['date'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($event['location'])): ?>
                        <div><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></div>
                    <?php endif; ?>
                </div>
                <p class="small text-muted mb-3">Scan this QR at the event to confirm your attendance. Only students can check in.</p>
                <form method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" name="confirm" value="1" class="btn btn-confirm w-100">
                        <i class="fas fa-check-double me-2"></i>Confirm my attendance
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
