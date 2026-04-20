<?php
session_start();

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

function eventify_redirect_superadmin_unlock(string $type, string $message): void
{
    $openModal = trim((string)($_POST['open_modal'] ?? 'users'));
    $q = $type . '=' . urlencode($message) . '&open_modal=' . urlencode($openModal);
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?" . $q);
    exit();
}

// Only super admin can unlock accounts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    eventify_redirect_superadmin_unlock('error', 'Invalid request.');
}

// Validate user ID
$id = $_POST['id'] ?? '';

if (!ctype_digit((string)$id)) {
    eventify_redirect_superadmin_unlock('error', 'Invalid user ID');
}

// Unlock account
$stmt = $conn->prepare("
    UPDATE users 
    SET status = 'active', failed_attempts = 0 
    WHERE id = ? AND (status <> 'active' OR failed_attempts > 0)
");
$stmt->bind_param("i", $id);
$stmt->execute();
$changed = $stmt->affected_rows > 0;
if (!$changed) {
    $stmt->close();
    $conn->close();
    eventify_redirect_superadmin_unlock('error', 'No changes made. Account may already be active and unlocked.');
}

// Log activity
$actorId   = $_SESSION['user_id'] ?? null;
$actorRole = $_SESSION['role'] ?? null;
$details   = "Unlocked user ID {$id} (reset failed attempts)";
log_activity($conn, $actorId, $actorRole, 'user_unlocked', 'user', (int)$id, $details);

$conn->close();

eventify_redirect_superadmin_unlock('success', 'Account unlocked successfully');
