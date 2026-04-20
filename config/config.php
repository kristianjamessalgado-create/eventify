<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/school_events');
}

if (!defined('EVENTIFY_SMS_PROVIDER')) {
    define('EVENTIFY_SMS_PROVIDER', 'semaphore');
}

if (!defined('SEMAPHORE_API_KEY')) {
    
    define('SEMAPHORE_API_KEY', 'PASTE_NEW_SEMAPHORE_API_KEY_HERE');
}

if (!defined('SEMAPHORE_SENDER_NAME')) {
   
    define('SEMAPHORE_SENDER_NAME', '');
}


if (!defined('EVENTIFY_SMTP_HOST')) {
    define('EVENTIFY_SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('EVENTIFY_SMTP_PORT')) {
    define('EVENTIFY_SMTP_PORT', 587);
}
if (!defined('EVENTIFY_SMTP_USERNAME')) {
    define('EVENTIFY_SMTP_USERNAME', 'bojiking31@gmail.com');
}
if (!defined('EVENTIFY_SMTP_PASSWORD')) {
   
define('EVENTIFY_SMTP_PASSWORD', 'jksbqqyigtshfuuy');
}
if (!defined('EVENTIFY_SMTP_FROM_EMAIL')) {
    define('EVENTIFY_SMTP_FROM_EMAIL', 'bojiking31@gmail.com');
}
if (!defined('EVENTIFY_SMTP_FROM_NAME')) {
    define('EVENTIFY_SMTP_FROM_NAME', 'EVENTIFY');
}
if (!defined('EVENTIFY_SMTP_ALLOW_INSECURE_TLS')) {
    
    define('EVENTIFY_SMTP_ALLOW_INSECURE_TLS', true);
}

$error = $error ?? '';
$success = $success ?? '';
?>