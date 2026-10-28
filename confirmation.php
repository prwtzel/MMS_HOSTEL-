<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Booking confirmation';
$code = trim($_GET['code'] ?? '');
if (!$code || empty($_SESSION['last_booking_code']) || !hash_equals((string)$_SESSION['last_booking_code'], $code)) {
  http_response_code(403); exit('For privacy, this confirmation is available only in the session that created it. <a href=\"rooms.php\">Browse rooms</a>');
}
$stmt = db()->prepare("SELECT b.*, r.name AS room_name, r.image_url FROM bookings b JOIN rooms r ON r.id=b.room_id WHERE b.booking_code=?");
$stmt->execute([$code]); $booking = $stmt->fetch();
if (!$booking) { http_response_code(404); exit('Booking not found.'); }
$depositDue = round((float)$booking['total_amount'] * (float)DOWN_PAYMENT_PERCENT / 100, 2);
$methodLabels = ['gcash'=>'GCash', 'bank_transfer'=>'Bank transfer', 'cash'=>'Cash at hostel', 'online_demo'=>'Online payment (demo only)'];
include __DIR__ . '/includes/header.php';
?>
<section class="confirmation-section container">
  <div class="confirmation-icon">✓</div><p class="eyebrow">RESERVATION REQUEST RECEIVED</p><h1>Your stay is <em>taking shape.</em></h1><p class="confirmation-lead">Thank you, <?= e($booking['guest_name']) ?>. Your booking is pending until staff verifies your down payment.</p>
  <div class="confirmation-card"><img src="<?= e($booking['image_url']) ?>" alt="<?= e($booking['room_name']) ?>"><div class="confirmation-details"><span class="status-pill"><?= e(strtoupper($booking['status'])) ?></span><h2><?= e($booking['room_name']) ?></h2><p>Booking reference</p><strong class="booking-code"><?= e($booking['booking_code']) ?></strong><div class="confirmation-facts"><div><small>CHECK IN</small><strong><?= date('M j, Y',strtotime($booking['check_in'])) ?></strong></div><div><small>CHECK OUT</small><strong><?= date('M j, Y',strtotime($booking['check_out'])) ?></strong></div><div><small>GUESTS</small><strong><?= (int)$booking['guests'] ?></strong></div><div><small>TOTAL</small><strong><?= money($booking['total_amount']) ?></strong></div></div><p class="payment-status">Payment method: <strong><?= e($methodLabels[$booking['payment_method']] ?? $booking['payment_method']) ?></strong></p>
  <p><strong>Required down payment (<?= (int)DOWN_PAYMENT_PERCENT ?>%):</strong> <?= money($depositDue) ?></p>
  <?php if($booking['payment_method']==='gcash'): ?><div class="summary-note"><strong>GCash payment details</strong><br>Account name: <?= e(GCASH_ACCOUNT_NAME) ?><br>GCash number: <?= e(GCASH_NUMBER) ?><br>Replace these sample details in config/config.php with your real account before accepting payments.</div><?php elseif($booking['payment_method']==='bank_transfer'): ?><div class="summary-note"><strong>Bank transfer details</strong><br>Bank: <?= e(BANK_NAME) ?><br>Account name: <?= e(BANK_ACCOUNT_NAME) ?><br>Account number: <?= e(BANK_ACCOUNT_NUMBER) ?><br>Replace these sample details in config/config.php with your real account before accepting payments.</div><?php elseif($booking['payment_method']==='cash'): ?><div class="summary-note">Please pay the down payment to hostel staff in person and provide booking reference <?= e($booking['booking_code']) ?>.</div><?php else: ?><div class="summary-note">This online payment option is a demo only. No money is collected. A real payment gateway must be connected before it can accept online payments.</div><?php endif; ?>
  <p class="notice notice-warning">Status: <strong><?= e(strtoupper(str_replace('_',' ',$booking['status']))) ?></strong>. After staff verifies the required down payment, your reservation will be confirmed and an email will be sent to <?= e($booking['guest_email']) ?> (once SMTP is configured).</p>
  </div></div><p class="muted confirmation-note">Please save your booking reference. Email confirmation is sent after staff verifies the required down payment; configure SMTP first in config/config.php.</p><a class="text-link" href="<?= BASE_URL ?>/rooms.php">← Back to rooms</a>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
