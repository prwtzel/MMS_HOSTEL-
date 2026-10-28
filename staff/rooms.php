<?php
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $id = (int)($_POST['room_id'] ?? 0);
        if ($id > 0) {
            $q = $pdo->prepare('UPDATE rooms SET is_active = 1 - is_active WHERE id = ?');
            $q->execute([$id]);
        }
        redirect('/staff/rooms.php?updated=1');
    }

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['room_type'] ?? 'private';
        $price = filter_var($_POST['price_per_night'] ?? null, FILTER_VALIDATE_FLOAT);
        $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT);
        $bed = trim($_POST['bed_info'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $amenities = trim($_POST['amenities'] ?? '');
        $image = trim($_POST['image_url'] ?? '');

        if ($name === '' || !in_array($type, ['private', 'shared'], true)
            || $price === false || $price <= 0
            || $capacity === false || $capacity < 1 || $capacity > 20
            || !filter_var($image, FILTER_VALIDATE_URL)
            || strtolower((string)parse_url($image, PHP_URL_SCHEME)) !== 'https'
        ) {
            $error = 'Please enter a room name, valid room type, positive price, capacity from 1 to 20, and an HTTPS image URL.';
        } else {
            $q = $pdo->prepare(
                'INSERT INTO rooms (name, room_type, price_per_night, capacity, bed_info, description, amenities, image_url, is_active, featured)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0)'
            );
            $q->execute([$name, $type, $price, $capacity, $bed, $description, $amenities, $image]);
            redirect('/staff/rooms.php?added=1');
        }
    }
}

$rooms = $pdo->query('SELECT * FROM rooms ORDER BY id DESC')->fetchAll();
$pageTitle = 'Staff room management';
include __DIR__ . '/../includes/header.php';
?>
<section class="admin-page container">
  <div class="admin-heading">
    <div>
      <p class="eyebrow">MMS HOSTEL · STAFF PORTAL</p>
      <h1>Manage <em>room availability.</em></h1>
      <p class="muted">Add rooms to the collection and show or hide rooms from public search.</p>
    </div>
    <div class="admin-actions">
      <a class="button button-outline" href="<?= BASE_URL ?>/staff/index.php">← Staff dashboard</a>
      <a class="text-link" href="<?= BASE_URL ?>/staff/logout.php">Sign out ↗</a>
    </div>
  </div>

  <?php if (isset($_GET['added'])): ?><div class="notice notice-success">Room added successfully and marked active.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="notice notice-success">Room availability updated.</div><?php endif; ?>
  <?php if ($error): ?><div class="notice notice-warning"><?= e($error) ?></div><?php endif; ?>

  <div class="admin-room-layout">
    <div class="admin-table-wrap">
      <div class="table-heading"><h2>Room inventory</h2></div>
      <div class="table-scroll">
        <table>
          <thead><tr><th>Room</th><th>Type</th><th>Price</th><th>Capacity</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($rooms as $r): ?>
            <tr>
              <td><strong><?= e($r['name']) ?></strong></td>
              <td><?= e($r['room_type']) ?></td>
              <td><?= money($r['price_per_night']) ?></td>
              <td><?= (int)$r['capacity'] ?></td>
              <td><?= (int)$r['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>">
                  <button class="small-button" type="submit"><?= (int)$r['is_active'] === 1 ? 'Hide' : 'Activate' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rooms): ?><tr><td colspan="6">No rooms have been added yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="add-room-card">
      <p class="eyebrow">GROW YOUR COLLECTION</p>
      <h2>Add a room</h2>
      <form method="post" class="booking-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <label for="name">Room name</label><input id="name" name="name" required maxlength="150">
        <label for="room_type">Room type</label><select id="room_type" name="room_type"><option value="private">Private</option><option value="shared">Shared</option></select>
        <label for="price_per_night">Price per night (₱)</label><input id="price_per_night" name="price_per_night" type="number" min="1" step="0.01" required>
        <label for="capacity">Guest capacity</label><input id="capacity" name="capacity" type="number" min="1" max="20" value="2" required>
        <label for="bed_info">Bed information</label><input id="bed_info" name="bed_info" maxlength="200" placeholder="1 queen bed">
        <label for="description">Description</label><textarea id="description" name="description" rows="3" required maxlength="2000"></textarea>
        <label for="amenities">Amenities (comma-separated)</label><input id="amenities" name="amenities" maxlength="1000" placeholder="Wi-Fi, Air conditioning">
        <label for="image_url">Image URL (HTTPS)</label><input id="image_url" name="image_url" type="url" placeholder="https://..." required>
        <button class="button button-dark full-width" type="submit">Add room</button>
      </form>
    </div>
  </div>
  <p class="fine-print">New rooms are active by default. Hiding a room removes it from public room listings but does not delete its database record or booking history.</p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
