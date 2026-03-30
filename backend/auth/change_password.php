<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Please login first."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("Invalid request."));
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$current = (string)($_POST['current_password'] ?? '');
$new = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');
$next = trim((string)($_POST['next'] ?? ''));
$from = trim((string)($_POST['from'] ?? ($_GET['from'] ?? '')));

if ($userId <= 0) {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Session expired."));
    exit();
}

if ($new !== $confirm) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("New passwords do not match.") . "&next=" . urlencode($next) . "&from=" . urlencode($from));
    exit();
}
if (!preg_match('/[A-Z]/', $new) || !preg_match('/[\W_]/', $new) || strlen($new) < 8) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("Password must contain at least 1 uppercase, 1 special character, and 8 characters.") . "&next=" . urlencode($next) . "&from=" . urlencode($from));
    exit();
}

$stmt = $conn->prepare("SELECT password, COALESCE(must_change_password,0) AS must_change_password FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("Could not verify account."));
    exit();
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("User not found."));
    exit();
}

$stored = (string)($row['password'] ?? '');
$forceReset = ((int)($row['must_change_password'] ?? 0) === 1);
if (!$forceReset) {
    $validCurrent = false;
    if (password_verify($current, $stored)) {
        $validCurrent = true;
    } elseif (strlen($stored) === 64 && ctype_xdigit($stored) && hash_equals($stored, hash('sha256', $current))) {
        $validCurrent = true;
    }
    if (!$validCurrent) {
        header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("Current password is incorrect.") . "&next=" . urlencode($next) . "&from=" . urlencode($from));
        exit();
    }
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$up = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
if (!$up) {
    header("Location: " . BASE_URL . "/views/change_password.php?error=" . urlencode("Could not update password."));
    exit();
}
$up->bind_param("si", $newHash, $userId);
$up->execute();
$up->close();

if ($next !== '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowedPrefix = $scheme . '://' . $host . BASE_URL;
    $isSameOriginAbsolute = (strpos($next, $allowedPrefix) === 0);
    $isSafeRelative = (strpos($next, '/') === 0 && strpos($next, BASE_URL) === 0);
    if ($isSameOriginAbsolute || $isSafeRelative) {
        header("Location: " . $next);
        exit();
    }
}

header("Location: " . BASE_URL . "/index.php");
exit();
