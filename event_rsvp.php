<?php
/**
 * RSVP list (all registrations) for an event — organizer / admin / super_admin.
 * CSV export: ?id=1&export=csv
 */
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

$stmt = $conn->prepare("SELECT id, title, date, location, organizer_id FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header('Location: ' . BASE_URL . '?error=Event not found');
    exit();
}

$role = $_SESSION['role'] ?? '';
if ($role === 'organizer' && (int) $event['organizer_id'] !== (int) $_SESSION['user_id']) {
    header('Location: ' . BASE_URL . '?error=Access denied');
    exit();
}

$rows = [];
$st = $conn->prepare("
    SELECT r.user_id, r.registration_date, r.status, r.time_in, u.name, u.email, u.user_id AS school_id
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.registration_date ASC
");
$st->bind_param("i", $event_id);
$st->execute();
$res = $st->get_result();
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$st->close();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="event_rsvp_' . (int) $event['id'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['#', 'Name', 'Email', 'Student/Staff ID', 'Registered at', 'RSVP status', 'Check-in time']);
    $i = 1;
    foreach ($rows as $row) {
        fputcsv($out, [
            $i++,
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['school_id'] ?? '',
            $row['registration_date'] ?? '',
            $row['status'] ?? '',
            $row['time_in'] ?? '',
        ]);
    }
    fclose($out);
    $conn->close();
    exit();
}

$conn->close();

$back_url = BASE_URL . '/backend/auth/dashboardorganizer.php';
if ($role === 'admin') {
    $back_url = BASE_URL . '/backend/admin/dashboard.php';
}
if ($role === 'super_admin') {
    $back_url = BASE_URL . '/backend/super_admin/dashboardsuperadmin.php';
}

$pageTitle = htmlspecialchars($event['title']) . ' – RSVP list';
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
        .card { max-width: 900px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>RSVP list</h5>
            <small class="text-muted"><?= htmlspecialchars($event['title']) ?> · <?= htmlspecialchars($event['date'] ?? '') ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($back_url) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="?id=<?= (int)$event_id ?>&export=csv" class="btn btn-success btn-sm"><i class="fas fa-download"></i> Export CSV</a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <p class="text-muted p-4 mb-0">No registrations yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID</th>
                            <th>Registered</th>
                            <th>Check-in</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n = 1; foreach ($rows as $row): ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['school_id'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['registration_date'] ?? '') ?></td>
                                <td class="small"><?= htmlspecialchars($row['time_in'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
