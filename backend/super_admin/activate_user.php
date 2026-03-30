<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../config/csrf.php';
include __DIR__ . '/../../backend/lib/activity_logger.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header("Location: " . BASE_URL . "/views/login.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid request."));
    exit();
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Invalid user ID."));
    exit();
}

$userStmt = $conn->prepare("SELECT id, email, status, failed_attempts FROM users WHERE id = ? LIMIT 1");
if (!$userStmt) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Failed to validate user for activation."));
    exit();
}
$userStmt->bind_param("i", $id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();
if (!$user) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User not found."));
    exit();
}

$isLockedAccount = ((int)($user['failed_attempts'] ?? 0) >= 5);
if ($isLockedAccount) {
    // Locked accounts require a completed reactivation OTP verification first.
    $otpStmt = $conn->prepare("
        SELECT id
        FROM account_email_otps
        WHERE purpose = 'reactivate'
          AND user_id = ?
          AND used_at IS NOT NULL
        ORDER BY used_at DESC, id DESC
        LIMIT 1
    ");
    if (!$otpStmt) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Cannot activate locked account: OTP validation unavailable."));
        exit();
    }
    $otpStmt->bind_param("i", $id);
    $otpStmt->execute();
    $otpVerified = $otpStmt->get_result()->fetch_assoc();
    $otpStmt->close();

    if (!$otpVerified) {
        $conn->close();
        header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Cannot activate yet: reactivation OTP not verified."));
        exit();
    }
}

$stmt = $conn->prepare("UPDATE users SET status = 'active', failed_attempts = 0 WHERE id = ?");
if (!$stmt) {
    $conn->close();
    header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("Failed to prepare activation."));
    exit();
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

log_activity(
    $conn,
    (int)($_SESSION['user_id'] ?? 0),
    (string)($_SESSION['role'] ?? ''),
    'user_activated',
    'user',
    $id,
    "Activated pending user ID {$id}"
);

$conn->close();
header("Location: " . BASE_URL . "/backend/super_admin/dashboardsuperadmin.php?success=" . urlencode("User activated."));
exit();
