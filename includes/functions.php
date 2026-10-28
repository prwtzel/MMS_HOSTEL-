
<?php
require_once __DIR__ . '/../config/config.php';

// Prevent private pages from being cached after logout.
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|string $amount): string {
    return '₱' . number_format((float)$amount, 2);
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';

    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Your session token expired. Go back, refresh the page, and try again.');
    }
}

function redirect(string $path): never {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function logged_in_admin(): bool {
    return !empty($_SESSION['user_id'])
        && ($_SESSION['user_role'] ?? '') === 'admin';
}

function logged_in_staff(): bool {
    return !empty($_SESSION['user_id'])
        && ($_SESSION['user_role'] ?? '') === 'staff';
}

function require_admin(): void {
    if (!logged_in_admin()) {
        redirect('/admin/login.php');
    }
}

function require_staff(): void {
    if (!logged_in_staff()) {
        redirect('/staff/login.php');
    }
}

function require_any_user(): void {
    if (!logged_in_admin() && !logged_in_staff()) {
        redirect('/admin/login.php');
    }
}

function date_is_valid(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function nights_between(string $checkin, string $checkout): int {
    return (int)((strtotime($checkout) - strtotime($checkin)) / 86400);
}

function room_is_available(
    PDO $pdo,
    int $roomId,
    string $checkin,
    string $checkout
): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE room_id = ?
        AND status NOT IN ('cancelled')
        AND check_in < ?
        AND check_out > ?
    ");

    $stmt->execute([$roomId, $checkout, $checkin]);
    return (int)$stmt->fetchColumn() === 0;
}

function require_staff_or_admin(): void {
    if (!logged_in_admin() && !logged_in_staff()) {
        redirect('/admin/login.php');
    }
}
