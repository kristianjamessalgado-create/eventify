<?php
session_start();
if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}
include __DIR__ . '/config/db.php';
include __DIR__ . '/config/config.php';

$allowed_roles = ['super_admin', 'admin', 'organizer'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Access denied'));
    exit();
}

$event_id = (int) ($_GET['id'] ?? 0);
if ($event_id < 1) {
    header('Location: ' . BASE_URL . '?error=Invalid event');
    exit();
}

$stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, status, checkin_token, organizer_id FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    header('Location: ' . BASE_URL . '?error=Event not found');
    exit();
}

// Ensure event has a check-in token (for older events created before this feature)
if (empty($event['checkin_token'])) {
    $event['checkin_token'] = bin2hex(random_bytes(16));
    $up = $conn->prepare("UPDATE events SET checkin_token = ? WHERE id = ?");
    $up->bind_param("si", $event['checkin_token'], $event_id);
    $up->execute();
    $up->close();
}

$base_host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
// QR sends users to landing page first with check-in token; logged-in students are then redirected to check-in
$landing_url = $base_host . BASE_URL . '/index.php?t=' . urlencode($event['checkin_token']);
$qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($landing_url);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event QR - <?= htmlspecialchars($event['title']) ?> | EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 2rem; background: #f8f9fa; }
        .qr-card { max-width: 420px; margin: 0 auto; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
        .qr-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 1.25rem; text-align: center; }
        .qr-body { padding: 1.5rem; background: #fff; }
        .qr-body img { display: block; margin: 0 auto 1rem; border: 4px solid #eee; border-radius: 12px; }
        .checkin-url { font-size: 0.8rem; word-break: break-all; color: #6c757d; }
        .event-meta { color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="qr-card card border-0">
        <div class="qr-header">
            <h1 class="h5 mb-0"><i class="fas fa-qrcode me-2"></i>Event Check-in QR</h1>
            <p class="small mb-0 opacity-90 mt-1">Display this at the event for students to scan</p>
        </div>
        <div class="qr-body">
            <h5 class="mb-2"><?= htmlspecialchars($event['title']) ?></h5>
            <div class="event-meta mb-3">
                <?php if (!empty($event['date'])): ?>
                    <div><i class="fas fa-calendar-day me-2"></i><?= date('M j, Y', strtotime($event['date'])) ?><?php if (!empty($event['start_time'])): ?> · <?= date('g:i A', strtotime($event['start_time'])) ?><?php endif; ?></div>
                <?php endif; ?>
                <?php if (!empty($event['location'])): ?>
                    <div><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></div>
                <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars($qr_image_url) ?>" alt="QR Code" width="300" height="300">
            <p class="small text-muted mb-1">Students scan this QR to confirm attendance.</p>
            <p class="checkin-url mb-0" title="<?= htmlspecialchars($landing_url) ?>"><?= htmlspecialchars($landing_url) ?></p>
            <div class="mt-3">
                <?php
                $role = $_SESSION['role'] ?? '';
                $back_url = BASE_URL . '/backend/auth/dashboardorganizer.php';
                if ($role === 'admin') $back_url = BASE_URL . '/backend/admin/dashboard.php';
                if ($role === 'super_admin') $back_url = BASE_URL . '/backend/super_admin/dashboardsuperadmin.php';
                ?>
                <a href="<?= $back_url ?>" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
