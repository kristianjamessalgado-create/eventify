<?php
session_start();

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

// Only super admin can unlock accounts
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?error=" . urlencode("Invalid request."));
    exit();
}

// Validate user ID
$id = $_POST['id'] ?? '';

if (!ctype_digit((string)$id)) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?error=" . urlencode("Invalid user ID"));
    exit();
}

// Unlock account
$stmt = $conn->prepare("
    UPDATE users 
    SET status = 'active', failed_attempts = 0 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();

// Log activity
$actorId   = $_SESSION['user_id'] ?? null;
$actorRole = $_SESSION['role'] ?? null;
$details   = "Unlocked user ID {$id} (reset failed attempts)";
log_activity($conn, $actorId, $actorRole, 'user_unlocked', 'user', (int)$id, $details);

$conn->close();

header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Account unlocked successfully"));
exit();
