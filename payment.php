<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Demo payment';
$code = trim($_GET['code'] ?? '');
if (!$code || empty($_SESSION['last_booking_code']) || !hash_equals((string)$_SESSION['last_booking_code'], $code)) { http_response_code(403); exit('Booking session not found.'); }
$pdo=db(); $stmt=$pdo->prepare("SELECT b.*,r.name room_name FROM bookings b JOIN rooms r ON r.id=b.room_id WHERE booking_code=? AND payment_method='online_demo'"); $stmt->execute([$code]); $booking=$stmt->fetch();
if(!$booking){http_response_code(404);exit('Demo payment not found.');}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$up=$pdo->prepare("UPDATE bookings SET payment_status='demo_completed' WHERE booking_code=?");$up->execute([$code]);redirect('/payment.php?code='.urlencode($code).'&done=1');}
include __DIR__ . '/includes/header.php';
?>
<section class="confirmation-section container"><div class="confirmation-icon"><?= isset($_GET['done'])?'✓':'₱' ?></div><p class="eyebrow">DEMO CHECKOUT</p><h1><?= isset($_GET['done'])?'Demo payment complete.':'Complete your demo payment.' ?></h1><p class="confirmation-lead">This is a simulated payment screen for development. No payment is processed and no card details should be entered.</p><div class="demo-payment-card"><p class="eyebrow">RESERVATION</p><h2><?= e($booking['room_name']) ?></h2><p>Reference: <strong><?= e($booking['booking_code']) ?></strong></p><div class="summary-price"><?= money($booking['total_amount']) ?></div><p class="muted">Demo status: <?= e($booking['payment_status']) ?></p><?php if(!isset($_GET['done']) && $booking['payment_status']!=='demo_completed'): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="button button-dark" type="submit">Simulate successful payment</button></form><?php else: ?><span class="status-pill">DEMO COMPLETE — NOT A REAL CHARGE</span><?php endif; ?></div><a class="text-link" href="<?= BASE_URL ?>/index.php">Return to homepage ↗</a></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
