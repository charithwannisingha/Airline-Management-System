-- ============================================================
--  AMS — Airlines Management System
--  Database Setup Script
--  Author: K.G.S.H. Madumali (AMP/IT/2324/F/104)
--  Hardy ATI Ampara · 2023/2024
-- ============================================================

CREATE DATABASE IF NOT EXISTS ams_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ams_db;

-- ─── USERS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(100) NOT NULL UNIQUE,
    phone        VARCHAR(20),
    address      TEXT,
    role         ENUM('admin','passenger') NOT NULL DEFAULT 'passenger',
    last_login   DATETIME,
    created_at   DATETIME NOT NULL DEFAULT NOW(),
    INDEX idx_username (username),
    INDEX idx_email    (email)
);

-- ─── AIRCRAFT ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS aircraft (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    registration     VARCHAR(20) NOT NULL UNIQUE,
    model            VARCHAR(100) NOT NULL,
    manufacturer     VARCHAR(100),
    capacity         INT NOT NULL DEFAULT 180,
    economy_seats    INT NOT NULL DEFAULT 150,
    business_seats   INT NOT NULL DEFAULT 24,
    first_seats      INT NOT NULL DEFAULT 6,
    maintenance_status ENUM('operational','maintenance','retired') NOT NULL DEFAULT 'operational',
    last_maintenance DATE,
    next_maintenance DATE,
    created_at       DATETIME NOT NULL DEFAULT NOW()
);

-- ─── FLIGHTS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS flights (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    flight_number    VARCHAR(10) NOT NULL UNIQUE,
    aircraft_id      INT,
    origin           CHAR(3)     NOT NULL,
    destination      CHAR(3)     NOT NULL,
    departure_time   DATETIME    NOT NULL,
    arrival_time     DATETIME    NOT NULL,
    price_economy    DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_business   DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_first      DECIMAL(10,2) NOT NULL DEFAULT 0,
    capacity         INT NOT NULL DEFAULT 180,
    status           ENUM('scheduled','boarding','on_time','delayed','cancelled','completed') NOT NULL DEFAULT 'scheduled',
    created_at       DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (aircraft_id) REFERENCES aircraft(id) ON DELETE SET NULL,
    INDEX idx_origin      (origin),
    INDEX idx_destination (destination),
    INDEX idx_departure   (departure_time)
);

-- ─── BOOKINGS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref        VARCHAR(20) NOT NULL UNIQUE,
    user_id            INT NOT NULL,
    flight_id          INT NOT NULL,
    seat_number        VARCHAR(5)  NOT NULL,
    class              ENUM('economy','business','first') NOT NULL DEFAULT 'economy',
    total_price        DECIMAL(10,2) NOT NULL,
    passenger_first    VARCHAR(100) NOT NULL,
    passenger_last     VARCHAR(100) NOT NULL,
    passenger_email    VARCHAR(100) NOT NULL,
    passenger_phone    VARCHAR(20),
    doc_type           ENUM('nic','passport') NOT NULL DEFAULT 'nic',
    doc_number         VARCHAR(50) NOT NULL,
    nationality        VARCHAR(100) DEFAULT 'Sri Lankan',
    status             ENUM('confirmed','cancelled','checked_in','completed') NOT NULL DEFAULT 'confirmed',
    created_at         DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seat (flight_id, seat_number),
    INDEX idx_user_id   (user_id),
    INDEX idx_flight_id (flight_id),
    INDEX idx_booking_ref (booking_ref)
);

-- ─── CREW ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS crew (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    employee_id  VARCHAR(20)  NOT NULL UNIQUE,
    full_name    VARCHAR(100) NOT NULL,
    role         ENUM('captain','first_officer','cabin_crew','purser') NOT NULL,
    license_no   VARCHAR(50),
    availability ENUM('available','on_duty','rest','leave') NOT NULL DEFAULT 'available',
    created_at   DATETIME NOT NULL DEFAULT NOW()
);

-- ─── FLIGHT CREW ASSIGNMENTS ─────────────────────────────────
CREATE TABLE IF NOT EXISTS flight_crew (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    flight_id  INT NOT NULL,
    crew_id    INT NOT NULL,
    role       VARCHAR(50),
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE,
    FOREIGN KEY (crew_id)   REFERENCES crew(id)    ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (flight_id, crew_id)
);

-- ─── DEMO DATA ───────────────────────────────────────────────

-- Users (passwords are bcrypt hashes)
-- admin123 → $2y$12$... | user123 → $2y$12$...
INSERT IGNORE INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$12$8VJ0XGtNoxFjYUBrJjQb5OmX3S0l6JjRfYKYxBhCtnmjuHiYx.Ptu', 'System Administrator', 'admin@hardyati.lk', 'admin'),
('user',  '$2y$12$u8bMJlfJ2pUzTDnLm.SqhuKxI.mhQXGkiZ.VHGPpEXnk2dFi.fT.a', 'K.G.S.H. Madumali',     'madumali@email.com',    'passenger');
-- Note: Run php -r "echo password_hash('admin123', PASSWORD_DEFAULT);" to regenerate hashes

-- Aircraft
INSERT IGNORE INTO aircraft (registration, model, manufacturer, capacity, economy_seats, business_seats, first_seats, maintenance_status) VALUES
('4R-ABE', 'Airbus A320-Neo',  'Airbus',  180, 150, 24, 6,  'operational'),
('4R-ABF', 'Boeing 737-800',   'Boeing',  162, 132, 24, 6,  'maintenance'),
('4R-ABG', 'Airbus A321',      'Airbus',  220, 184, 30, 6,  'operational'),
('4R-ABH', 'Boeing B777-300',  'Boeing',  396, 340, 42, 14, 'operational');

-- Flights (future dates)
INSERT IGNORE INTO flights (flight_number, aircraft_id, origin, destination, departure_time, arrival_time, price_economy, price_business, price_first, capacity, status) VALUES
('HA101', 1, 'CMB', 'DXB', DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 08:30:00'), DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 11:45:00'), 42500, 98000, 185000, 180, 'scheduled'),
('HA204', 2, 'CMB', 'SIN', DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 09:15:00'), DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 14:50:00'), 55000, 120000, 220000, 162, 'scheduled'),
('HA312', 3, 'CMB', 'KUL', DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 10:00:00'), DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 15:40:00'), 48000, 105000, 195000, 220, 'scheduled'),
('HA417', 1, 'CMB', 'BOM', DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 11:30:00'), DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 13:15:00'), 22000, 58000, 110000, 180, 'on_time'),
('HA520', 4, 'CMB', 'LHR', DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 14:00:00'), DATE_FORMAT(NOW() + INTERVAL 1 DAY, '%Y-%m-%d 20:30:00'), 95000, 245000, 485000, 396, 'scheduled');

-- Crew
INSERT IGNORE INTO crew (employee_id, full_name, role, license_no, availability) VALUES
('CPT001', 'Capt. M. Fernando',    'captain',        'LCA-2010-001', 'available'),
('FO001',  'F/O R. Jayawardena',   'first_officer',  'LCA-2015-045', 'available'),
('CC001',  'N. Dissanayake',       'cabin_crew',     'SLA-2018-112', 'on_duty'),
('CPT002', 'Capt. D. Silva',       'captain',        'LCA-2008-007', 'available'),
('CC002',  'P. Wickramasinghe',    'cabin_crew',     'SLA-2020-088', 'available');

ALTER TABLE users ADD COLUMN loyalty_points INT DEFAULT 0;

ALTER TABLE bookings ADD COLUMN refund_amount DECIMAL(10,2) DEFAULT 0.00;