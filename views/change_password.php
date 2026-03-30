<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/views/login.php?error=" . urlencode("Please login first."));
    exit();
}

$from = trim((string)($_GET['from'] ?? ''));
$next = trim((string)($_GET['next'] ?? ''));
$msg = trim((string)($_GET['msg'] ?? ''));
$error = trim((string)($_GET['error'] ?? ''));
$userId = (int)($_SESSION['user_id'] ?? 0);
$forceReset = false;
try {
    $col = $conn->query("SHOW COLUMNS FROM users LIKE 'must_change_password'");
    $hasCol = (bool)($col && $col->num_rows > 0);
    if ($hasCol && $userId > 0) {
        $st = $conn->prepare("SELECT must_change_password FROM users WHERE id = ? LIMIT 1");
        if ($st) {
            $st->bind_param("i", $userId);
            $st->execute();
            $r = $st->get_result()->fetch_assoc();
            $st->close();
            $forceReset = ((int)($r['must_change_password'] ?? 0) === 1);
        }
    }
} catch (Throwable $e) {
    $forceReset = ($from === 'reactivation' || $from === 'required');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - EVENTIFY</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
    <style>
        .cp-wrap { max-width: 520px; margin: 42px auto; }
        .cp-note { margin: 0 0 10px; color: #dbeafe; font-size: 13px; text-align: center; }
        .cp-msg { width: 100%; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; font-size: 13px; }
        .cp-msg.ok { background: rgba(22,163,74,0.2); border: 1px solid rgba(74,222,128,0.5); color: #bbf7d0; }
        .cp-msg.err { background: rgba(220,38,38,0.2); border: 1px solid rgba(248,113,113,0.5); color: #fecaca; }
    </style>
</head>
<body>
<video autoplay muted loop playsinline id="bgVideo">
    <source src="<?= BASE_URL ?>/assets/video/adminvid.mov" type="video/quicktime">
    <source src="<?= BASE_URL ?>/assets/video/adminvid.mov" type="video/mp4">
</video>

<div class="container active cp-wrap">
    <div class="form-box register" style="display:flex;">
        <h1>Change Password</h1>
        <?php if ($from === 'reactivation'): ?>
            <p class="cp-note">Your account was reactivated. For security, set a new password now.</p>
        <?php endif; ?>
        <?php if ($msg !== ''): ?><div class="cp-msg ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="cp-msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form action="<?= BASE_URL ?>/backend/auth/change_password.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
            <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
            <?php if (!$forceReset): ?>
                <div class="input-box">
                    <input type="password" name="current_password" placeholder="Current Password" required>
                </div>
            <?php endif; ?>
            <div class="input-box">
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            </div>
            <small class="field-hint">New password must be at least 8 chars with 1 uppercase and 1 special character.</small>
            <button type="submit" class="btn">Update Password</button>
        </form>
    </div>
</div>
</body>
</html>
