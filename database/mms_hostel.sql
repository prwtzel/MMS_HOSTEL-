CREATE DATABASE IF NOT EXISTS mms_hostel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mms_hostel;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  room_type ENUM('private','shared') NOT NULL DEFAULT 'private',
  price_per_night DECIMAL(10,2) NOT NULL,
  capacity TINYINT UNSIGNED NOT NULL DEFAULT 2,
  bed_info VARCHAR(160) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  amenities VARCHAR(500) NOT NULL DEFAULT '',
  image_url VARCHAR(500) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(30) NOT NULL UNIQUE,
  room_id INT UNSIGNED NOT NULL,
  guest_name VARCHAR(160) NOT NULL,
  guest_email VARCHAR(190) NOT NULL,
  guest_phone VARCHAR(50) DEFAULT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guests TINYINT UNSIGNED NOT NULL,
  nights SMALLINT UNSIGNED NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('pay_at_hotel','online_demo') NOT NULL DEFAULT 'pay_at_hotel',
  payment_status ENUM('pay_at_hotel','pending_demo','demo_completed') NOT NULL DEFAULT 'pay_at_hotel',
  status ENUM('confirmed','checked_in','completed','cancelled') NOT NULL DEFAULT 'confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id),
  INDEX idx_booking_dates (room_id, check_in, check_out, status),
  INDEX idx_booking_email (guest_email)
) ENGINE=InnoDB;

-- Demo password: ChangeMe123!
-- Replace this hash/password immediately for any deployment.
INSERT INTO admins (name,email,password_hash) VALUES
('MMS Administrator','admin@mmshostel.local','$2y$12$k1g0qKKYbqGnVDd7jKyo1u1Np0ZiPtWbHec4gkdhqQNl6UV50LrX.');

INSERT INTO rooms (name,room_type,price_per_night,capacity,bed_info,description,amenities,image_url,is_active,featured) VALUES
('The Signature Suite','private',4200.00,2,'1 king bed','A serene private suite with warm textures, considered details, and room to slow down.','King bed, Air conditioning, Private bathroom, Wi-Fi, Smart TV','https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=85',1,1),
('Garden View Deluxe','private',3200.00,2,'1 queen bed','A bright and inviting room with a calm palette and a restful garden-inspired feel.','Queen bed, Air conditioning, Private bathroom, Wi-Fi','https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=85',1,1),
('The Comfort Twin','private',2600.00,2,'2 single beds','A flexible, comfortable room for friends, colleagues, or travel companions.','Twin beds, Air conditioning, Private bathroom, Wi-Fi','https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85',1,1),
('Social Stay Dorm','shared',850.00,4,'4 single bunks','A clean, sociable shared room with practical comforts for budget-conscious explorers.','Bunk beds, Air conditioning, Shared bathroom, Wi-Fi, Personal locker','https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=85',1,0),
('Quiet Corner Room','private',2100.00,1,'1 single bed','A compact private room designed for solo stays and peaceful nights.','Single bed, Air conditioning, Private bathroom, Wi-Fi','https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85',1,0);
