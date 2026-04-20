<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../lib/staff_messaging.php';
require_once __DIR__ . '/../lib/activity_logger.php';

header('Content-Type: application/json; charset=utf-8');

$uid = (int)($_SESSION['user_id'] ?? 0);
$role = strtolower((string)($_SESSION['role'] ?? ''));
if ($uid < 1 || !in_array($role, ['admin', 'organizer'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Access denied']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_validate()) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

$recipientId = (int)($_POST['recipient_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
if ($recipientId < 1 || $recipientId === $uid) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid recipient.']);
    exit;
}
if ($body === '' || mb_strlen($body) > 8000) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Message must be 1–8000 characters.']);
    exit;
}

if (!eventify_staff_messaging_pair_allowed($conn, $uid, $recipientId)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'You can only message admins or organizers.']);
    exit;
}

if (!eventify_staff_messages_ensure_table($conn)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Messaging is unavailable.']);
    exit;
}

$stmt = $conn->prepare('INSERT INTO staff_messages (sender_id, recipient_id, body) VALUES (?, ?, ?)');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save message.']);
    exit;
}
$stmt->bind_param('iis', $uid, $recipientId, $body);
$ok = $stmt->execute();
$newId = (int)$stmt->insert_id;
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save message.']);
    exit;
}

log_activity($conn, $uid, $role, 'staff_message_sent', 'user', $recipientId, 'Staff message (admin↔organizer)');

// In-app notification for recipient (optional table)
try {
    $snippet = mb_strimwidth($body, 0, 120, '…');
    $title = $role === 'admin' ? 'Message from Admin' : 'Message from Organizer';
    $ins = $conn->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'staff_message', ?, ?)");
    if ($ins) {
        $ins->bind_param('iss', $recipientId, $title, $snippet);
        $ins->execute();
        $ins->close();
    }
} catch (Throwable $e) {
    // ignore if notifications schema differs
}

$conn->close();

echo json_encode(['ok' => true, 'id' => $newId]);
