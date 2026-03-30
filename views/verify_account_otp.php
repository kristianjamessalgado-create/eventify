<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$email = $_GET['email'] ?? '';
$purpose = ($_GET['purpose'] ?? 'register') === 'reactivate' ? 'reactivate' : 'register';
$is_embed = isset($_GET['embed']) && $_GET['embed'] === '1';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $is_embed ? 'embed-mode' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - EVENTIFY</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body class="<?= $is_embed ? 'embed-mode' : '' ?>">
<div class="container active" style="max-width: 520px; margin: <?= $is_embed ? '10px auto' : '48px auto' ?>;">
    <div class="form-box register" style="width: 100%; position: static; transform: none; opacity: 1;">
        <h1>Verify OTP</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <p style="margin-bottom: 14px; color: #ddd;">Enter the OTP sent to <strong><?= htmlspecialchars($email) ?></strong>.</p>
        <form action="<?= BASE_URL ?>/backend/auth/verify_account_otp.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="purpose" value="<?= htmlspecialchars($purpose) ?>">
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="input-box">
                <input type="text" name="otp_code" placeholder="6-digit OTP" required maxlength="6" pattern="\d{6}">
            </div>
            <button type="submit" class="btn">Verify</button>
        </form>
        <?php if (!$is_embed): ?>
            <div style="margin-top: 14px;">
                <a class="switch-btn" href="<?= BASE_URL ?>/views/login.php?form=login" style="display:inline-block;">Back to login</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
