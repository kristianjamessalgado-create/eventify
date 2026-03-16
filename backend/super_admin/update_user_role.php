<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

// Only super admin can change roles
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php");
    exit();
}

if (!csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?error=" . urlencode("Invalid request. Please try again."));
    exit();
}

$userId   = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$newRole  = $_POST['new_role'] ?? '';

$allowedRoles = ['super_admin', 'admin', 'organizer', 'multimedia', 'student'];

if ($userId <= 0 || !in_array($newRole, $allowedRoles, true)) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid role change request."));
    exit();
}

// Prevent changing own role to avoid locking yourself out accidentally
if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("You cannot change your own role."));
    exit();
}

// Fetch current role
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($currentRole);
$stmt->fetch();
$stmt->close();

if (!$currentRole) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User not found."));
    exit();
}

if ($currentRole === $newRole) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Role is already set to {$newRole}."));
    exit();
}

$stmtUpdate = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
if (!$stmtUpdate) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Failed to prepare role update."));
    exit();
}

$stmtUpdate->bind_param("si", $newRole, $userId);
if ($stmtUpdate->execute()) {
    $stmtUpdate->close();

    // Log activity
    $actorId   = $_SESSION['user_id'] ?? null;
    $actorRole = $_SESSION['role'] ?? null;
    $details   = "Changed user ID {$userId} role from {$currentRole} to {$newRole}";
    log_activity($conn, $actorId, $actorRole, 'user_role_changed', 'user', $userId, $details);

    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User role updated."));
    exit();
}

$stmtUpdate->close();
$conn->close();

header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Failed to update user role."));
exit();

