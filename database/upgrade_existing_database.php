<?php
declare(strict_types=1);
/**
 * MMS Hostel additive migration for an EXISTING local database.
 * Preserves existing room, booking, and account rows. Run once, then delete this file.
 */
require_once __DIR__ . '/../config/config.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = db();
    $tableExists = static function (PDO $pdo, string $table): bool {
        $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $q->execute([$table]);
        return (int)$q->fetchColumn() > 0;
    };
    $columnExists = static function (PDO $pdo, string $table, string $column): bool {
        $q = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
        $q->execute([$table, $column]);
        return (int)$q->fetchColumn() > 0;
    };

    foreach (['admins', 'rooms', 'bookings'] as $required) {
        if (!$tableExists($pdo, $required)) {
            throw new RuntimeException("Required table '{$required}' was not found in database '" . DB_NAME . "'. Check config/config.php and select the existing database.");
        }
    }
    if (!$columnExists($pdo, 'admins', 'password_hash')) {
        throw new RuntimeException("The admins table has no password_hash column. Stop here and send the table structure so the migration can be adapted safely.");
    }

    if (!$columnExists($pdo, 'admins', 'role')) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN role ENUM('admin','staff') NOT NULL DEFAULT 'admin'");
        echo "Added admins.role\n";
    } else { echo "admins.role already exists — kept unchanged\n"; }

    if (!$columnExists($pdo, 'admins', 'is_active')) {
        $pdo->exec('ALTER TABLE admins ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1');
        echo "Added admins.is_active\n";
    } else { echo "admins.is_active already exists — kept unchanged\n"; }

    // Extend existing booking columns safely for deposits and manual payment methods.
    if (!$columnExists($pdo, 'bookings', 'payment_reference')) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_reference VARCHAR(120) DEFAULT NULL AFTER payment_status");
        echo "Added bookings.payment_reference\n";
    } else { echo "bookings.payment_reference already exists — kept unchanged\n"; }
    $pdo->exec("ALTER TABLE bookings MODIFY payment_method ENUM('pay_at_hotel','online_demo','cash','gcash','bank_transfer') NOT NULL DEFAULT 'gcash'");
    $pdo->exec("ALTER TABLE bookings MODIFY payment_status ENUM('pay_at_hotel','pending_demo','demo_completed','pending_deposit','deposit_received','paid') NOT NULL DEFAULT 'pending_deposit'");
    $pdo->exec("ALTER TABLE bookings MODIFY status ENUM('pending_payment','confirmed','checked_in','completed','cancelled') NOT NULL DEFAULT 'pending_payment'");
    echo "Booking fields now support pending down payments and cash/GCash/bank transfer choices.\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_records (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      booking_id BIGINT UNSIGNED NOT NULL,
      amount DECIMAL(10,2) NOT NULL,
      method ENUM('cash','gcash_manual','bank_transfer','other','online_demo') NOT NULL DEFAULT 'cash',
      reference_code VARCHAR(120) DEFAULT NULL,
      status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
      notes VARCHAR(500) DEFAULT NULL,
      recorded_by INT UNSIGNED DEFAULT NULL,
      verified_by INT UNSIGNED DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      verified_at DATETIME DEFAULT NULL,
      INDEX idx_payment_status (status),
      INDEX idx_payment_booking (booking_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("ALTER TABLE payment_records MODIFY method ENUM('cash','gcash_manual','bank_transfer','other','online_demo') NOT NULL DEFAULT 'cash'");
    echo "Payment records table is ready (existing table, if any, was preserved).\n";

    // Existing account records are never overwritten. Ensure the known demo admin is assigned admin role if present.
    $admin = $pdo->prepare("UPDATE admins SET role='admin' WHERE email=? AND role <> 'admin'");
    $admin->execute(['admin@mmshostel.local']);

    // Add a demo staff account only if this email is not already present.
    $q = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
    $q->execute(['staff@mmshostel.local']);
    if (!$q->fetchColumn()) {
        $insert = $pdo->prepare('INSERT INTO admins (name,email,password_hash,role,is_active) VALUES (?,?,?,?,1)');
        $insert->execute(['MMS Demo Staff', 'staff@mmshostel.local', password_hash('StaffStart123!', PASSWORD_DEFAULT), 'staff']);
        echo "Created demo staff login: staff@mmshostel.local / StaffStart123!\n";
    } else {
        echo "Existing staff@mmshostel.local account found — password and account details were not changed.\n";
    }

    echo "\nSUCCESS: Additive upgrade complete. Existing rooms, bookings, and accounts were not dropped.\n";
    echo "IMPORTANT: Delete database/upgrade_existing_database.php from the web folder after this one-time run. Change demo passwords.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "UPGRADE STOPPED: " . $e->getMessage() . "\n";
    echo "No existing tables were dropped. If an ALTER succeeded before this error, rerunning this script is designed to be safe.\n";
}
