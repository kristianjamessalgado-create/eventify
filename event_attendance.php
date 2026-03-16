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

$stmt = $conn->prepare("SELECT id, title, date, start_time, end_time, location, organizer_id FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    header('Location: ' . BASE_URL . '?error=Event not found');
    exit();
}

$role = $_SESSION['role'] ?? '';
// Organizers may only view attendance for their own events
if ($role === 'organizer' && (int) $event['organizer_id'] !== (int) $_SESSION['user_id']) {
    header('Location: ' . BASE_URL . '?error=Access denied');
    exit();
}

// Attendees: students who checked in (status = present)
$attendees = [];
$st = $conn->prepare("
    SELECT r.user_id, r.time_in, u.name
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ? AND r.status = 'present'
    ORDER BY r.time_in ASC
");
$st->bind_param("i", $event_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $attendees[] = $row;
}
$st->close();

// Optional CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="event_attendance_' . (int)$event['id'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['#', 'Student Name', 'User ID', 'Check-in Time']);
    $i = 1;
    foreach ($attendees as $row) {
        fputcsv($out, [
            $i++,
            $row['name'] ?? '',
            $row['user_id'] ?? '',
            $row['time_in'] ?? '',
        ]);
    }
    fclose($out);
    $conn->close();
    exit();
}

$conn->close();

// Back link by role
$back_url = BASE_URL . '/backend/auth/dashboardorganizer.php';
if ($role === 'admin') {
    $back_url = BASE_URL . '/backend/admin/dashboard.php';
}
if ($role === 'super_admin') {
    $back_url = BASE_URL . '/backend/super_admin/dashboardsuperadmin.php';
}

$pageTitle = htmlspecialchars($event['title']) . ' – Attendance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | EVENTIFY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 1.5rem; background: #f8f9fa; }
        .att-card { max-width: 720px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .att-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 1.25rem; }
        .att-body { padding: 1.5rem; background: #fff; }
        .event-meta { color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-top: 0.25rem; }
        .table-attendees { margin-bottom: 0; }
        .table-attendees th { background: #f1f5f9; font-weight: 600; }
    </style>
</head>
<body>
    <div class="att-card card border-0">
        <div class="att-header">
            <h1 class="h5 mb-0"><i class="fas fa-clipboard-check me-2"></i>Attendance</h1>
            <p class="event-meta mb-0"><?= htmlspecialchars($event['title']) ?></p>
            <?php if (!empty($event['date'])): ?>
                <p class="event-meta mb-0"><?= date('l, M j, Y', strtotime($event['date'])) ?><?= !empty($event['location']) ? ' · ' . htmlspecialchars($event['location']) : '' ?></p>
            <?php endif; ?>
        </div>
        <div class="att-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted"><?= count($attendees) ?> <?= count($attendees) === 1 ? 'attendee' : 'attendees' ?></span>
                <div class="d-flex gap-2">
                    <?php if (!empty($attendees)): ?>
                        <a href="<?= htmlspecialchars(BASE_URL . '/event_attendance.php?id=' . (int)$event['id'] . '&export=csv') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-export me-1"></i>Export CSV
                        </a>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($back_url) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to dashboard</a>
                </div>
            </div>
            <?php if (empty($attendees)): ?>
                <p class="text-muted mb-0">No one has checked in yet. Students can scan the event QR to confirm attendance.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-attendees table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Check-in time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendees as $i => $a): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($a['name'] ?? '—') ?></td>
                                    <td><?= $a['time_in'] ? date('M j, Y g:i A', strtotime($a['time_in'])) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
