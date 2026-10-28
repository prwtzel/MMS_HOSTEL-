<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'A more thoughtful stay';
$featured = db()->query("SELECT * FROM rooms WHERE is_active=1 ORDER BY featured DESC, price_per_night ASC LIMIT 3")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-image"></div>
  <div class="container hero-content">
    <p class="eyebrow light-eyebrow"><span></span> YOUR SPACE TO UNWIND</p>
    <h1>Stay a little<br><em>closer to calm.</em></h1>
    <p class="hero-copy">Considered comfort. Beautifully simple stays. A welcoming place to pause, recharge, and feel at home.</p>
    <a class="button button-light" href="<?= BASE_URL ?>/rooms.php">Discover your stay <span>↗</span></a>
  </div>
  <div class="hero-note"><span>01 / 03</span><span>Comfort, with intention</span></div>
</section>
<section class="search-section">
  <div class="container">
    <form class="search-panel" action="<?= BASE_URL ?>/rooms.php" method="get">
      <div class="search-field"><label for="check_in">CHECK IN</label><input id="check_in" name="check_in" type="date" required></div>
      <div class="search-field"><label for="check_out">CHECK OUT</label><input id="check_out" name="check_out" type="date" required></div>
      <div class="search-field"><label for="guests">GUESTS</label><select id="guests" name="guests"><option value="1">1 guest</option><option value="2" selected>2 guests</option><option value="3">3 guests</option><option value="4">4 guests</option><option value="5">5 guests</option><option value="6">6 guests</option></select></div>
      <button class="button button-dark" type="submit">Find your room <span>→</span></button>
    </form>
  </div>
</section>
<section class="section container" id="experience">
  <div class="section-heading"><div><p class="eyebrow">A PLACE TO FEEL GOOD</p><h2>Thoughtful spaces.<br><em>Restful moments.</em></h2></div><p class="section-intro">Every detail is chosen to make your stay feel effortless — from soft textures to the little comforts that help you settle in.</p></div>
  <div class="feature-grid">
    <article class="feature-card"><div class="feature-icon">✧</div><h3>Comfort first</h3><p>Welcoming rooms, restful beds, and the essentials done right.</p></article>
    <article class="feature-card"><div class="feature-icon">⌂</div><h3>Feel at home</h3><p>Warm hospitality and thoughtfully arranged spaces to unwind.</p></article>
    <article class="feature-card"><div class="feature-icon">◷</div><h3>Easy stays</h3><p>A straightforward booking experience from search to check-in.</p></article>
  </div>
</section>
<section class="section rooms-section">
  <div class="container">
    <div class="section-heading"><div><p class="eyebrow">THE ROOM COLLECTION</p><h2>Find your <em>kind of comfort.</em></h2></div><a class="text-link" href="<?= BASE_URL ?>/rooms.php">View all rooms ↗</a></div>
    <div class="room-grid">
    <?php foreach ($featured as $room): ?>
      <article class="room-card">
        <a class="room-image-wrap" href="<?= BASE_URL ?>/rooms.php"><img src="<?= e($room['image_url']) ?>" alt="<?= e($room['name']) ?>" loading="lazy"><span class="image-label"><?= $room['room_type'] === 'private' ? 'PRIVATE ROOM' : 'SHARED STAY' ?></span></a>
        <div class="room-card-body"><div class="room-meta"><span><?= (int)$room['capacity'] ?> guests</span><span><?= e($room['bed_info']) ?></span></div><h3><?= e($room['name']) ?></h3><p><?= e($room['description']) ?></p><div class="room-card-bottom"><span><strong><?= money($room['price_per_night']) ?></strong> / night</span><a href="<?= BASE_URL ?>/rooms.php">Explore ↗</a></div></div>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="quote-section"><div class="container quote-inner"><p class="eyebrow light-eyebrow">THE MMS DIFFERENCE</p><blockquote>“A good stay is more than a room. It’s how you feel when you’re there.”</blockquote><span>THE MMS HOSTEL PHILOSOPHY</span></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
