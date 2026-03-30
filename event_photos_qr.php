<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

include __DIR__ . '/config/db.php';
include __DIR__ . '/config/config.php';

// Only multimedia, admin, super_admin can generate photo QR
$allowed_roles = ['multimedia', 'admin', 'super_admin'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
    header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Access denied'));
    exit();
}

$event_id = (int) ($_GET['id'] ?? 0);
if ($event_id < 1) {
    header('Location: ' . BASE_URL . '?error=Invalid event');
    exit();
}

$stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, status, department, checkin_token FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    $conn->close();
    header('Location: ' . BASE_URL . '?error=Event not found');
    exit();
}

// Ensure the event has a token we can reuse for photos
if (empty($event['checkin_token'])) {
    $event['checkin_token'] = bin2hex(random_bytes(16));
    $up = $conn->prepare("UPDATE events SET checkin_token = ? WHERE id = ?");
    $up->bind_param("si", $event['checkin_token'], $event_id);
    $up->execute();
    $up->close();
}

// Build gallery URL (students will see photos via this link)
$base_host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$gallery_url = $base_host . BASE_URL . '/photo_gallery.php?t=' . urlencode($event['checkin_token']);
$qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($gallery_url);

$conn->close();

$role = $_SESSION['role'] ?? '';
$back_url = BASE_URL . '/backend/auth/dashboard_multimedia.php';
if ($role === 'admin') {
    $back_url = BASE_URL . '/backend/admin/dashboard.php';
} elseif ($role === 'super_admin') {
    $back_url = BASE_URL . '/backend/super_admin/dashboardsuperadmin.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Photos QR - <?= htmlspecialchars($event['title']) ?> | EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 2rem; background: #f3f4f6; }
        .qr-card { max-width: 440px; margin: 0 auto; border-radius: 16px; box-shadow: 0 10px 40px rgba(15,23,42,0.15); overflow: hidden; }
        .qr-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #fff; padding: 1.25rem; text-align: center; }
        .qr-body { padding: 1.5rem; background: #fff; }
        .qr-body img { display: block; margin: 0 auto 1rem; border: 4px solid #e5e7eb; border-radius: 12px; background:#fff; }
        .gallery-url { font-size: 0.8rem; word-break: break-all; color: #6b7280; }
        .event-meta { color: #6b7280; font-size: 0.9rem; }
        .dept-pill { display:inline-flex; align-items:center; padding:0.22rem 0.55rem; border-radius:999px; font-size:0.75rem; border:1px solid #d1d5db; background:#f9fafb; margin-top:0.35rem;}
    </style>
</head>
<body>
    <div class="qr-card card border-0">
        <div class="qr-header">
            <h1 class="h5 mb-1"><i class="fas fa-qrcode me-2"></i>Event Photos QR</h1>
            <p class="small mb-0 opacity-75">Print or display this so students can view the official photos.</p>
        </div>
        <div class="qr-body">
            <h5 class="mb-1"><?= htmlspecialchars($event['title']) ?></h5>
            <div class="event-meta mb-2">
                <?php if (!empty($event['date'])): ?>
                    <div><i class="fas fa-calendar-day me-2"></i><?= date('M j, Y', strtotime($event['date'])) ?><?php if (!empty($event['start_time'])): ?> · <?= date('g:i A', strtotime($event['start_time'])) ?><?php endif; ?></div>
                <?php endif; ?>
                <?php if (!empty($event['location'])): ?>
                    <div><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></div>
                <?php endif; ?>
                <?php if (!empty($event['department']) && $event['department'] !== 'ALL'): ?>
                    <div class="dept-pill"><i class="fas fa-building me-1"></i><?= htmlspecialchars($event['department']) ?> only</div>
                <?php else: ?>
                    <div class="dept-pill"><i class="fas fa-users me-1"></i>All departments</div>
                <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars($qr_image_url) ?>" alt="QR Code for event photos" width="300" height="300">
            <p class="small text-muted mb-1">Students scan this QR to open the photo gallery (students only, filtered by department).</p>
            <p class="gallery-url mb-0" title="<?= htmlspecialchars($gallery_url) ?>"><?= htmlspecialchars($gallery_url) ?></p>
            <div class="mt-3">
                <a href="<?= $back_url ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>

