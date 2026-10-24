CREATE DATABASE IF NOT EXISTS bengkel_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bengkel_service;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff','mechanic') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(190) NULL,
  address TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_customer_name (name),
  INDEX idx_customer_phone (phone)
) ENGINE=InnoDB;

CREATE TABLE vehicles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  plate_number VARCHAR(20) NOT NULL UNIQUE,
  brand VARCHAR(80) NOT NULL,
  model VARCHAR(100) NOT NULL,
  year SMALLINT UNSIGNED NULL,
  color VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicle_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_vehicle_brand_model (brand, model)
) ENGINE=InnoDB;

CREATE TABLE services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(14,2) NOT NULL DEFAULT 0,
  duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  service_id BIGINT UNSIGNED NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  complaint TEXT NOT NULL,
  status ENUM('Baru','Dikonfirmasi','Diproses','Selesai','Dibatalkan') NOT NULL DEFAULT 'Baru',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_booking_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_booking_service FOREIGN KEY (service_id) REFERENCES services(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_booking_schedule (booking_date, booking_time),
  INDEX idx_booking_status (status)
) ENGINE=InnoDB;

CREATE TABLE work_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NULL,
  customer_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  mechanic_name VARCHAR(120) NULL,
  odometer INT UNSIGNED NULL,
  complaint TEXT NOT NULL,
  diagnosis TEXT NULL,
  labor_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
  parts_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('Menunggu','Dikerjakan','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  started_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_wo_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_wo_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_wo_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_wo_status (status)
) ENGINE=InnoDB;

CREATE TABLE spare_parts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100) NULL,
  stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  min_stock INT UNSIGNED NOT NULL DEFAULT 3,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_part_name (name)
) ENGINE=InnoDB;

CREATE TABLE invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(60) NOT NULL UNIQUE,
  work_order_id BIGINT UNSIGNED NOT NULL UNIQUE,
  customer_id BIGINT UNSIGNED NOT NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('Belum Lunas','Sebagian','Lunas') NOT NULL DEFAULT 'Belum Lunas',
  due_date DATE NULL,
  issued_at DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoice_work_order FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_invoice_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_invoice_status (status)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  method ENUM('Tunai','Transfer','QRIS','Kartu','Lainnya') NOT NULL DEFAULT 'Tunai',
  paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255) NULL,
  CONSTRAINT fk_payment_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_payment_date (paid_at)
) ENGINE=InnoDB;

INSERT INTO services (name,description,price,duration_minutes,is_active) VALUES
('Servis berkala','Pemeriksaan dan perawatan rutin kendaraan.',250000,90,1),
('Ganti oli mesin','Penggantian oli mesin dan pemeriksaan singkat.',85000,30,1),
('Tune up','Pemeriksaan sistem mesin dan penyetelan.',300000,120,1),
('Servis rem','Pemeriksaan kampas, cakram, dan sistem pengereman.',175000,60,1),
('Pemeriksaan AC','Pemeriksaan performa pendingin kabin.',150000,60,1),
('Spooring & balancing','Penyelarasan roda dan penyeimbangan ban.',225000,75,1);

INSERT INTO spare_parts (sku,name,category,stock_qty,unit_price,min_stock) VALUES
('OIL-001','Oli mesin 1L','Pelumas',12,85000,4),
('FLT-001','Filter oli','Filter',8,45000,3),
('BRK-001','Kampas rem depan','Sistem rem',5,225000,2),
('SPK-001','Busi standar','Pengapian',20,35000,5);
