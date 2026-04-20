<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/staff_messaging.php';

$uid = (int)($_SESSION['user_id'] ?? 0);
$role = strtolower((string)($_SESSION['role'] ?? ''));
if ($uid < 1 || !in_array($role, ['admin', 'organizer'], true)) {
    header('Location: ' . BASE_URL . '/views/login.php?error=' . rawurlencode('Sign in to use messages.'));
    exit;
}

$peerRole = ($role === 'admin') ? 'organizer' : 'admin';
$pageTitle = ($role === 'admin') ? 'Messages — Organizers' : 'Messages — Admins';
$dashboardHref = ($role === 'admin')
    ? BASE_URL . '/backend/admin/dashboard.php'
    : BASE_URL . '/backend/auth/dashboardorganizer.php';

$peers = [];
$peersList = [];
$myName = '';
$messaging_error = null;

if (!eventify_staff_messages_ensure_table($conn)) {
    $messaging_error = 'Messaging is temporarily unavailable.';
    $conn->close();
    include __DIR__ . '/../../views/staff_messenger.php';
    exit;
}

$st = $conn->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
if ($st) {
    $st->bind_param('i', $uid);
    $st->execute();
    $r = $st->get_result();
    if ($r && ($row = $r->fetch_assoc())) {
        $myName = (string)($row['name'] ?? '');
    }
    $st->close();
}

$pq = $conn->prepare("SELECT id, name, email FROM users WHERE role = ? ORDER BY name ASC LIMIT 500");
if ($pq) {
    $pq->bind_param('s', $peerRole);
    $pq->execute();
    $pr = $pq->get_result();
    if ($pr) {
        while ($row = $pr->fetch_assoc()) {
            $peers[(int)$row['id']] = [
                'id' => (int)$row['id'],
                'name' => (string)($row['name'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'last_body' => null,
                'last_at' => null,
                'last_sender_id' => null,
                'unread_count' => 0,
            ];
        }
    }
    $pq->close();
}

$lastByPeer = [];
$lq = $conn->prepare("
    SELECT x.peer_id, m.body, m.created_at, m.sender_id AS last_sender_id
    FROM (
        SELECT IF(sender_id = ?, recipient_id, sender_id) AS peer_id, MAX(id) AS max_id
        FROM staff_messages
        WHERE sender_id = ? OR recipient_id = ?
        GROUP BY peer_id
    ) x
    INNER JOIN staff_messages m ON m.id = x.max_id
");
if ($lq) {
    $lq->bind_param('iii', $uid, $uid, $uid);
    $lq->execute();
    $lr = $lq->get_result();
    if ($lr) {
        while ($row = $lr->fetch_assoc()) {
            $pid = (int)($row['peer_id'] ?? 0);
            if ($pid > 0) {
                $lastByPeer[$pid] = $row;
            }
        }
    }
    $lq->close();
}

$uq = $conn->prepare('SELECT sender_id, COUNT(*) AS c FROM staff_messages WHERE recipient_id = ? AND read_at IS NULL GROUP BY sender_id');
if ($uq) {
    $uq->bind_param('i', $uid);
    $uq->execute();
    $ur = $uq->get_result();
    if ($ur) {
        while ($row = $ur->fetch_assoc()) {
            $sid = (int)($row['sender_id'] ?? 0);
            if (isset($peers[$sid])) {
                $peers[$sid]['unread_count'] = (int)($row['c'] ?? 0);
            }
        }
    }
    $uq->close();
}

foreach ($lastByPeer as $pid => $row) {
    if (!isset($peers[$pid])) {
        continue;
    }
    $peers[$pid]['last_body'] = (string)($row['body'] ?? '');
    $peers[$pid]['last_at'] = $row['created_at'] ?? null;
    $peers[$pid]['last_sender_id'] = isset($row['last_sender_id']) ? (int)$row['last_sender_id'] : null;
}

$peersList = array_values($peers);
usort($peersList, function ($a, $b) {
    $ta = $a['last_at'] ? strtotime((string)$a['last_at']) : 0;
    $tb = $b['last_at'] ? strtotime((string)$b['last_at']) : 0;
    if ($ta === $tb) {
        return strcasecmp($a['name'], $b['name']);
    }
    return $tb <=> $ta;
});

$initialWith = (int)($_GET['with'] ?? 0);
if ($initialWith > 0 && !isset($peers[$initialWith])) {
    $initialWith = 0;
}

$conn->close();

include __DIR__ . '/../../views/staff_messenger.php';
