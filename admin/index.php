
<?php
require_once __DIR__ . '/../includes/functions.php';

// SECURITY: Require admin login before showing the dashboard.
require_admin();

$pdo = db();

// Handle booking status updates.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int) ($_POST['booking_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'confirmed',
        'checked_in',
        'completed',
        'cancelled'
    ];

    if ($id > 0 && in_array($status, $allowedStatuses, true)) {
        // Check that the booking exists before updating it.
        $check = $pdo->prepare(
            "SELECT id FROM bookings WHERE id = ?"
        );
        $check->execute([$id]);

        if ($check->fetchColumn()) {
            $stmt = $pdo->prepare(
                "UPDATE bookings SET status = ? WHERE id = ?"
            );
            $stmt->execute([$status, $id]);

            $_SESSION['booking_update_message'] =
                'Reservation status updated successfully.';
        } else {
            $_SESSION['booking_update_message'] =
                'Booking not found.';
        }
    } else {
        $_SESSION['booking_update_message'] =
            'Invalid booking ID or status.';
    }

    redirect('/admin/index.php?updated=1');
}

$pageTitle = 'Admin dashboard';

$counts = $pdo->query("
    SELECT
        COUNT(*) AS bookings,
        COALESCE(SUM(status = 'confirmed'), 0) AS confirmed,
        COALESCE(SUM(payment_status = 'demo_completed'), 0) AS demo_paid
    FROM bookings
")->fetch();

$bookings = $pdo->query("
    SELECT b.*, r.name AS room_name
    FROM bookings b
    JOIN rooms r ON r.id = b.room_id
    ORDER BY b.created_at DESC
    LIMIT 100
")->fetchAll();

$rooms = $pdo->query(
    "SELECT * FROM rooms ORDER BY id DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<section class="admin-page container">
    <div class="admin-heading">
        <div>
            <p class="eyebrow">MMS HOSTEL MANAGEMENT</p>
            <h1>
                Good day,
                <em><?= e($_SESSION['admin_name'] ?? 'Admin') ?>.</em>
            </h1>
            <p class="muted">
                A clear view of your rooms and reservations.
            </p>
        </div>

        <div class="admin-actions">
            <a class="button button-outline"
               href="<?= BASE_URL ?>/admin/rooms.php">Manage rooms</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/admin/staff.php">Manage staff</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/admin/reservations.php">Reservations</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/admin/payments.php">Payments</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/admin/reports.php">Reports</a>

            <a class="text-link"
               href="<?= BASE_URL ?>/admin/logout.php">Sign out ↗</a>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success">
            <?= e(
                $_SESSION['booking_update_message']
                ?? 'Reservation status update processed.'
            ) ?>
        </div>
        <?php unset($_SESSION['booking_update_message']); ?>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <span>Total bookings</span>
            <strong><?= (int) $counts['bookings'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Confirmed bookings</span>
            <strong><?= (int) $counts['confirmed'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Demo payments completed</span>
            <strong><?= (int) $counts['demo_paid'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Active rooms</span>
            <strong>
                <?= (int) $pdo->query(
                    "SELECT COUNT(*) FROM rooms WHERE is_active = 1"
                )->fetchColumn() ?>
            </strong>
        </div>
    </div>

    <div class="feature-grid admin-module-links"
         style="margin-bottom:30px">

        <a class="feature-card"
           href="<?= BASE_URL ?>/admin/reservations.php">
            <div class="feature-icon">▣</div>
            <h3>Guest &amp; Reservations</h3>
            <p>
                Search guests, view booking details, and manage booking status.
            </p>
        </a>

        <a class="feature-card"
           href="<?= BASE_URL ?>/admin/payments.php">
            <div class="feature-icon">₱</div>
            <h3>Payment Recording</h3>
            <p>
                Record deposits and verify cash, GCash, or bank transfer manually.
            </p>
        </a>

        <a class="feature-card"
           href="<?= BASE_URL ?>/admin/reports.php">
            <div class="feature-icon">↗</div>
            <h3>Reports</h3>
            <p>
                Review booking totals, verified collections, occupancy,
                and payment status.
            </p>
        </a>
    </div>

    <div class="admin-table-wrap">
        <div class="table-heading">
            <div>
                <p class="eyebrow">RESERVATION OVERVIEW</p>
                <h2>Recent bookings</h2>
            </div>
            <span class="results-tag">Latest 100</span>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Reference / Guest</th>
                        <th>Room</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?= e($b['booking_code']) ?></strong>
                                <small>
                                    <?= e($b['guest_name']) ?><br>
                                    <?= e($b['guest_email']) ?>
                                </small>
                            </td>

                            <td><?= e($b['room_name']) ?></td>

                            <td>
                                <?= date('M j', strtotime($b['check_in'])) ?>
                                –
                                <?= date('M j, Y', strtotime($b['check_out'])) ?>
                                <small>
                                    <?= (int) $b['nights'] ?> night(s)
                                </small>
                            </td>

                            <td><?= money($b['total_amount']) ?></td>

                            <td>
                                <?= e(
                                    str_replace('_', ' ', $b['payment_status'])
                                ) ?>
                            </td>

                            <td>
                                <span class="status-pill">
                                    <?= e($b['status']) ?>
                                </span>
                            </td>

                            <td>
                                <form method="post" class="inline-status">
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e(csrf_token()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int) $b['id'] ?>"
                                    >

                                    <select name="status"
                                            aria-label="Booking status">
                                        <?php foreach (
                                            [
                                                'confirmed',
                                                'checked_in',
                                                'completed',
                                                'cancelled'
                                            ] as $s
                                        ): ?>
                                            <option
                                                value="<?= e($s) ?>"
                                                <?= $b['status'] === $s
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= e($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="small-button">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$bookings): ?>
                        <tr>
                            <td colspan="7">No bookings yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
