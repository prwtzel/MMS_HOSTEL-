<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Explore rooms';
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = max(1, min(6, (int)($_GET['guests'] ?? 1)));
$type = $_GET['type'] ?? 'all';
$maxPrice = (float)($_GET['max_price'] ?? 0);
$validDates = date_is_valid($checkIn) && date_is_valid($checkOut) && nights_between($checkIn, $checkOut) > 0;
$sql = "SELECT * FROM rooms WHERE is_active=1 AND capacity >= ?";
$params = [$guests];
if (in_array($type, ['private','shared'], true)) { $sql .= " AND room_type = ?"; $params[] = $type; }
if ($maxPrice > 0) { $sql .= " AND price_per_night <= ?"; $params[] = $maxPrice; }
$sql .= " ORDER BY featured DESC, price_per_night ASC";
$stmt = db()->prepare($sql); $stmt->execute($params); $rooms = $stmt->fetchAll();
if ($validDates) {
  $rooms = array_values(array_filter($rooms, fn($r) => room_is_available(db(), (int)$r['id'], $checkIn, $checkOut)));
}
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">THE ROOM COLLECTION</p><h1>Make yourself <em>at home.</em></h1><p>Explore thoughtfully designed spaces for every kind of stay.</p></div></section>
<section class="container listing-layout">
  <aside class="filter-panel"><h3>Find your room</h3><form method="get" action="<?= BASE_URL ?>/rooms.php">
    <label for="check_in">Check in</label><input id="check_in" name="check_in" type="date" value="<?= e($checkIn) ?>">
    <label for="check_out">Check out</label><input id="check_out" name="check_out" type="date" value="<?= e($checkOut) ?>">
    <label for="guests">Guests</label><select id="guests" name="guests"><?php for($i=1;$i<=6;$i++): ?><option value="<?= $i ?>" <?= $guests===$i?'selected':'' ?>><?= $i ?> <?= $i===1?'guest':'guests' ?></option><?php endfor; ?></select>
    <label for="type">Room style</label><select id="type" name="type"><option value="all">All room types</option><option value="private" <?= $type==='private'?'selected':'' ?>>Private room</option><option value="shared" <?= $type==='shared'?'selected':'' ?>>Shared room</option></select>
    <label for="max_price">Maximum price per night (₱)</label><input id="max_price" name="max_price" type="number" min="0" step="100" placeholder="No limit" value="<?= $maxPrice ?: '' ?>">
    <button class="button button-dark full-width" type="submit">Search rooms</button>
  </form><p class="filter-help">Tip: choose valid dates to see rooms available for your stay.</p></aside>
  <div class="listing-results"><div class="listing-top"><div><p class="eyebrow">YOUR NEXT STAY</p><h2><?= count($rooms) ?> spaces to explore</h2></div><span class="results-tag">Comfort, considered</span></div>
  <?php if (!$validDates && ($checkIn || $checkOut)): ?><div class="notice notice-warning">Please select a valid check-in and check-out date. Check-out must be after check-in.</div><?php endif; ?>
  <?php if (!$rooms): ?><div class="empty-state"><span>✧</span><h3>No rooms found</h3><p>Try different dates or loosen your filters.</p><a class="button button-dark" href="<?= BASE_URL ?>/rooms.php">Clear filters</a></div><?php endif; ?>
  <?php foreach ($rooms as $room): ?>
    <article class="listing-card"><img src="<?= e($room['image_url']) ?>" alt="<?= e($room['name']) ?>" loading="lazy"><div class="listing-card-content"><div class="room-meta"><span><?= e(strtoupper($room['room_type'])) ?> ROOM</span><span>Up to <?= (int)$room['capacity'] ?> guests</span></div><h3><?= e($room['name']) ?></h3><p><?= e($room['description']) ?></p><div class="amenities"><?= e($room['amenities']) ?></div><div class="listing-bottom"><div><strong><?= money($room['price_per_night']) ?></strong><span> / night</span><?php if($validDates): ?><small><?= nights_between($checkIn,$checkOut) ?> night(s) · <?= money($room['price_per_night'] * nights_between($checkIn,$checkOut)) ?> total</small><?php endif; ?></div><form action="<?= BASE_URL ?>/book.php" method="get"><input type="hidden" name="room_id" value="<?= (int)$room['id'] ?>"><input type="hidden" name="check_in" value="<?= e($checkIn) ?>"><input type="hidden" name="check_out" value="<?= e($checkOut) ?>"><input type="hidden" name="guests" value="<?= $guests ?>"><button class="button button-dark" type="submit">Book this room ↗</button></form></div></div></article>
  <?php endforeach; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
