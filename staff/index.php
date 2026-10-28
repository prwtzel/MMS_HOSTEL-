
<?php
require_once __DIR__ . '/../includes/functions.php';

require_staff();

$pdo = db();
$error = '';

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
        try {
            $q = $pdo->prepare(
                "UPDATE bookings SET status = ? WHERE id = ?"
            );

            $q->execute([$status, $id]);

            if ($q->rowCount() > 0) {
                $_SESSION['booking_update_message'] =
                    'Booking status updated successfully.';
            } else {
                $_SESSION['booking_update_message'] =
                    'No booking was changed. Check the booking ID and current status.';
            }
        } catch (PDOException $e) {
            $_SESSION['booking_update_message'] =
                'Unable to update the booking. Please try again.';
        }
    } else {
        $_SESSION['booking_update_message'] =
            'Invalid booking ID or status.';
    }

    redirect('/staff/index.php?updated=1');
}

$counts = $pdo->query("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'confirmed'), 0) AS confirmed,
        COALESCE(SUM(status = 'checked_in'), 0) AS checked_in
    FROM bookings
")->fetch();

$bookings = $pdo->query("
    SELECT b.*, r.name AS room_name
    FROM bookings b
    JOIN rooms r ON r.id = b.room_id
    ORDER BY b.created_at DESC
    LIMIT 100
")->fetchAll();

$pageTitle = 'Staff dashboard';

include __DIR__ . '/../includes/header.php';
?>

<section class="admin-page container">

    <div class="admin-heading">
        <div>
            <p class="eyebrow">MMS HOSTEL · STAFF PORTAL</p>

            <h1>
                Welcome,
                <em><?= e($_SESSION['user_name'] ?? 'Team member') ?>.</em>
            </h1>

            <p class="muted">
                Manage guest arrivals, departures, and reservation status.
            </p>
        </div>

        <div class="admin-actions">
            <a class="button button-outline"
               href="<?= BASE_URL ?>/staff/rooms.php">Manage rooms</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/staff/payments.php">Record payment</a>

            <a class="button button-outline"
               href="<?= BASE_URL ?>/rooms.php">View public site</a>

            <a class="text-link"
               href="<?= BASE_URL ?>/staff/logout.php">Sign out ↗</a>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success">
            <?= e(
                $_SESSION['booking_update_message']
                ?? 'Booking status update processed.'
            ) ?>
        </div>

        <?php unset($_SESSION['booking_update_message']); ?>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <span>Total reservations</span>
            <strong><?= (int) $counts['total'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Confirmed</span>
            <strong><?= (int) $counts['confirmed'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Currently checked in</span>
            <strong><?= (int) $counts['checked_in'] ?></strong>
        </div>

        <div class="stat-card">
            <span>Staff access</span>
            <strong>Active</strong>
        </div>
    </div>

    <div class="admin-table-wrap">
        <div class="table-heading">
            <div>
                <p class="eyebrow">GUEST OPERATIONS</p>
                <h2>Recent reservations</h2>
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
                        <th>Guests</th>
                        <th>Payment status</th>
                        <th>Booking status</th>
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

                                    <?php if (!empty($b['guest_phone'])): ?>
                                        <br><?= e($b['guest_phone']) ?>
                                    <?php endif; ?>
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

                            <td><?= (int) $b['guests'] ?></td>

                            <td>
                                <?= e(
                                    str_replace('_', ' ', $b['payment_status'])
                                ) ?>

                                <small><?= money($b['total_amount']) ?></small>
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

                                    <select name="status" aria-label="Booking status">
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
                            <td colspan="7">No reservations yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="fine-print">
        Staff can add rooms and activate or hide them from the Staff Room
        Management page. Staff cannot access admin staff-account controls.
        Payment records are displayed for reference; do not mark real
        payments as paid without verified provider confirmation.
    </p>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
