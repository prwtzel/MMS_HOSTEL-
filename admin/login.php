
<?php
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, go to the correct dashboard.
if (logged_in_admin()) {
    redirect('/admin/index.php');
}

if (logged_in_staff()) {
    redirect('/staff/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("
        SELECT id, name, email, password_hash, role, is_active
        FROM admins
        WHERE email = ? AND role = 'admin'
        LIMIT 1
    ");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (
        $user &&
        (int)$user['is_active'] === 1 &&
        password_verify($password, $user['password_hash'])
    ) {
        // Generate a new session ID after successful login.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];

        redirect('/admin/index.php');
    }

    $error = 'Incorrect admin email or password.';
}

$pageTitle = 'Admin sign in';
include __DIR__ . '/../includes/header.php';
?>

<section class="admin-login container">
    <div class="admin-login-card">
        <p class="eyebrow">MMS HOSTEL MANAGEMENT</p>

        <h1>Admin <em>sign in.</em></h1>

        <p class="muted">
            Access the owner and management dashboard.
        </p>

        <?php if ($error): ?>
            <div class="notice notice-warning">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="booking-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label for="email">Admin email</label>
            <input
                id="email"
                name="email"
                type="email"
                required
                autocomplete="username"
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
            >

            <button class="button button-dark full-width" type="submit">
                Sign in as admin
            </button>
        </form>

        <p class="fine-print">
            Staff must use the separate staff login.
        </p>

        <a class="text-link" href="<?= BASE_URL ?>/staff/login.php">
            Go to staff login ↗
        </a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
