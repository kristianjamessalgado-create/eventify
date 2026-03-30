<?php

function eventify_event_otp_table_ready(mysqli $conn): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $q = $conn->query("SHOW TABLES LIKE 'event_approval_otps'");
        $ready = (bool) ($q && $q->num_rows > 0);
    } catch (Throwable $e) {
        $ready = false;
    }
    return $ready;
}

function eventify_generate_otp_code(int $digits = 6): string
{
    $max = (10 ** $digits) - 1;
    $min = 10 ** ($digits - 1);
    return (string) random_int($min, $max);
}

function eventify_mask_email(string $email): string
{
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
        return '***';
    }
    $name = $parts[0];
    $domain = $parts[1];
    if (strlen($name) <= 2) {
        $nameMasked = substr($name, 0, 1) . '*';
    } else {
        $nameMasked = substr($name, 0, 2) . str_repeat('*', max(1, strlen($name) - 2));
    }
    return $nameMasked . '@' . $domain;
}

function eventify_mask_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);
    if (strlen($digits) < 4) {
        return '***';
    }
    return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
}
