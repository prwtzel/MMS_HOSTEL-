<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('APP_NAME', 'MMS Hostel');
define('BASE_URL', '/MMS_Hostel'); // Change if your folder name or deployment path differs.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'mms_hostel');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP local password is usually blank.

// Email delivery: configure these with your real SMTP provider before testing email.
// Gmail: use smtp.gmail.com, port 587, security STARTTLS, and a Google App Password.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURITY', 'tls'); // 'tls' for port 587 or 'ssl' for port 465
 define('SMTP_USERNAME', 'mmshostel@gmail.com');
define('SMTP_PASSWORD', 'flores20057');
define('MAIL_FROM_ADDRESS', 'mmshostel@gmail.com');
define('MAIL_FROM_NAME', 'MMS Hostel');

// Down-payment policy and manual transfer instructions. Change these to your real details.
define('DOWN_PAYMENT_PERCENT', 30);
define('GCASH_ACCOUNT_NAME', 'MMS Hostel');
define('GCASH_NUMBER', 'UPDATE_WITH_YOUR_GCASH_NUMBER');
define('BANK_NAME', 'UPDATE_WITH_YOUR_BANK_NAME');
define('BANK_ACCOUNT_NAME', 'MMS Hostel');
define('BANK_ACCOUNT_NUMBER', 'UPDATE_WITH_YOUR_BANK_ACCOUNT_NUMBER');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
