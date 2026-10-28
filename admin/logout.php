
<?php
require_once __DIR__ . '/../includes/functions.php';

// Clear all session data.
$_SESSION = [];

// Remove the session cookie from the browser.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

// Destroy the server-side session.
session_destroy();

// Return to the admin login page.
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
