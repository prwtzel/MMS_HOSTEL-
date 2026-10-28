<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Reserve your stay';
$pdo = db();
$roomId = (int)($_GET['room_id'] ?? $_POST['room_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id=? AND is_active=1"); $stmt->execute([$roomId]); $room = $stmt->fetch();
if (!$room) { http_response_code(404); exit('Room not found. <a href=\"rooms.php\">Return to rooms</a>'); }
$checkIn = $_POST['check_in'] ?? $_GET['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? $_GET['check_out'] ?? '';
$guests = max(1, min((int)$room['capacity'], (int)($_POST['guests'] ?? $_GET['guests'] ?? 1)));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim($_POST['guest_name'] ?? '');
  $email = trim($_POST['guest_email'] ?? '');
  $phone = trim($_POST['guest_phone'] ?? '');
  $payment = $_POST['payment_method'] ?? 'gcash';
  $paymentReference = trim($_POST['payment_reference'] ?? '');
  if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter your name and a valid email address.';
  elseif (!date_is_valid($checkIn) || !date_is_valid($checkOut) || nights_between($checkIn,$checkOut) < 1 || $checkIn < date('Y-m-d')) $error = 'Please select valid future dates; check-out must be after check-in.';
  elseif ($guests < 1 || $guests > (int)$room['capacity']) $error = 'Guest count exceeds this room’s capacity.';
  elseif (!in_array($payment, ['cash','gcash','bank_transfer','online_demo'], true)) $error = 'Please choose a valid payment method.';
  elseif (!room_is_available($pdo, $roomId, $checkIn, $checkOut)) $error = 'Sorry, this room is no longer available for those dates. Please choose other dates.';
  else {
    $nights = nights_between($checkIn,$checkOut);
    $total = $nights * (float)$room['price_per_night'];
    $code = 'MMS-' . strtoupper(bin2hex(random_bytes(4)));
    $paymentStatus = 'pending_deposit';
    $ins = $pdo->prepare("INSERT INTO bookings (booking_code,room_id,guest_name,guest_email,guest_phone,check_in,check_out,guests,nights,total_amount,payment_method,payment_status,payment_reference,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending_payment')");
    $ins->execute([$code,$roomId,$name,$email,$phone,$checkIn,$checkOut,$guests,$nights,$total,$payment,$paymentStatus,$paymentReference ?: null]);
    $_SESSION['last_booking_code'] = $code;
    redirect('/confirmation.php?code=' . urlencode($code));
  }
}
$pageTitle = 'Reserve ' . $room['name'];
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact"><div class="container"><p class="eyebrow">YOUR STAY, YOUR WAY</p><h1>Reserve your <em>space.</em></h1><p>A few details and your stay is one step closer.</p></div></section>
<section class="container booking-layout">
  <div class="booking-form-wrap"><h2>Guest details</h2><p class="muted">Fields marked with * are required.</p>
  <?php if($error): ?><div class="notice notice-warning"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="booking-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="room_id" value="<?= (int)$roomId ?>">
    <div class="form-row"><div><label for="guest_name">Full name *</label><input id="guest_name" name="guest_name" required minlength="2" value="<?= e($_POST['guest_name'] ?? '') ?>"></div><div><label for="guest_email">Email address *</label><input id="guest_email" name="guest_email" type="email" required value="<?= e($_POST['guest_email'] ?? '') ?>"></div></div>
    <label for="guest_phone">Phone number</label><input id="guest_phone" name="guest_phone" type="tel" placeholder="e.g. 09XX XXX XXXX" value="<?= e($_POST['guest_phone'] ?? '') ?>">
    <div class="form-row"><div><label for="check_in">Check-in *</label><input id="check_in" name="check_in" type="date" min="<?= date('Y-m-d') ?>" required value="<?= e($checkIn) ?>"></div><div><label for="check_out">Check-out *</label><input id="check_out" name="check_out" type="date" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" required value="<?= e($checkOut) ?>"></div></div>
    <label for="guests">Number of guests *</label><select id="guests" name="guests"><?php for($i=1;$i<=(int)$room['capacity'];$i++): ?><option value="<?= $i ?>" <?= $guests===$i?'selected':'' ?>><?= $i ?> <?= $i===1?'guest':'guests' ?></option><?php endfor; ?></select>
    <h3 class="form-section-title">Payment preference</h3>
    <p class="notice notice-warning">A <?= (int)DOWN_PAYMENT_PERCENT ?>% down payment is required. Your reservation stays pending until staff records and verifies the payment.</p>
    <label class="payment-choice"><input type="radio" name="payment_method" value="gcash" <?= ($_POST['payment_method'] ?? 'gcash')==='gcash'?'checked':'' ?>><span><strong>GCash</strong><small>Send your down payment using the GCash details shown after booking.</small></span></label>
    <label class="payment-choice"><input type="radio" name="payment_method" value="bank_transfer" <?= ($_POST['payment_method'] ?? '')==='bank_transfer'?'checked':'' ?>><span><strong>Bank transfer</strong><small>Transfer your down payment and keep the transaction reference.</small></span></label>
    <label class="payment-choice"><input type="radio" name="payment_method" value="cash" <?= ($_POST['payment_method'] ?? '')==='cash'?'checked':'' ?>><span><strong>Cash</strong><small>Pay the down payment to hostel staff in person.</small></span></label>
    <label class="payment-choice"><input type="radio" name="payment_method" value="online_demo" <?= ($_POST['payment_method'] ?? '')==='online_demo'?'checked':'' ?>><span><strong>Online payment (demo only)</strong><small>No real payment is processed until a real payment provider is integrated.</small></span></label>
    <label for="payment_reference">Payment reference (if already paid)</label><input id="payment_reference" name="payment_reference" maxlength="120" value="<?= e($_POST['payment_reference'] ?? '') ?>" placeholder="GCash/bank transaction reference">
    <button class="button button-dark full-width" type="submit">Confirm reservation ↗</button>
    <p class="fine-print">By continuing, you agree that the details you provide may be used to manage your reservation.</p>
  </form></div>
  <aside class="booking-summary"><img src="<?= e($room['image_url']) ?>" alt="<?= e($room['name']) ?>"><div class="summary-body"><p class="eyebrow">YOUR SELECTED ROOM</p><h3><?= e($room['name']) ?></h3><p><?= e($room['bed_info']) ?> · Up to <?= (int)$room['capacity'] ?> guests</p><div class="summary-price"><?= money($room['price_per_night']) ?><span> / night</span></div><div class="summary-note">A <?= (int)DOWN_PAYMENT_PERCENT ?>% down payment is required. Your booking is confirmed after staff verifies the payment.</div></div></aside>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
