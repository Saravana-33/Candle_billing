CREATE DATABASE IF NOT EXISTS billing_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE billing_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) DEFAULT '',
  address VARCHAR(255) DEFAULT ''
);

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  cost DECIMAL(10,2) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  date DATETIME NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('PAID','UNPAID') NOT NULL DEFAULT 'UNPAID',
  payment_mode VARCHAR(20) DEFAULT '-',
  FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE IF NOT EXISTS bill_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  item_name VARCHAR(100) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);

INSERT IGNORE INTO items (name, price, cost) VALUES
('Decorative Lotus', 675.00, 450.00),
('Pillar Candle (Large)', 250.00, 150.00),
('Scented Jar Candle', 350.00, 220.00),
('Birthday Candles (Pack)', 60.00, 30.00),
('Diya Set (6 pcs)', 180.00, 110.00),
('Wax Melts (Box)', 220.00, 130.00),
('Taper Candles (Pair)', 90.00, 50.00),
('Floating Candles (Set)', 300.00, 190.00),
('Votive Candles (Set of 4)', 160.00, 95.00),
('Tealight Candles (Pack of 12)', 120.00, 70.00),
('Rose Scented Candle', 280.00, 170.00),
('Vanilla Jar Candle', 320.00, 200.00),
('Jasmine Candle', 260.00, 155.00),
('Candle Gift Box', 850.00, 560.00),
('LED Diya (Pair)', 140.00, 80.00),
('Wedding Candle (Pair)', 450.00, 290.00),
('Candle Making Kit', 650.00, 420.00),
('Citronella Candle', 200.00, 120.00),
('Pillar Candle (Small)', 140.00, 85.00);