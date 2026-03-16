<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

// Only super admin can deactivate accounts
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid user ID"));
    exit();
}

$id = (int)$_GET['id'];

// Prevent deactivating own account
if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("You cannot deactivate your own account."));
    exit();
}

$stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Log activity
$actorId   = $_SESSION['user_id'] ?? null;
$actorRole = $_SESSION['role'] ?? null;
$details   = "Deactivated user ID {$id}";
log_activity($conn, $actorId, $actorRole, 'user_deactivated', 'user', (int)$id, $details);

$conn->close();

header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User deactivated."));
exit();

