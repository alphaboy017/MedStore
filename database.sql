-- Create database
CREATE DATABASE IF NOT EXISTS medical_store;
USE medical_store;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Medicines table
CREATE TABLE IF NOT EXISTS medicines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    batch_no VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    expiry_date DATE NOT NULL
);

-- Bills table
CREATE TABLE IF NOT EXISTS bills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_id INT NOT NULL,
    qty INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

-- Insert sample admin (password: admin123)
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$8K1p/a0dL1LXMIgZ5n0Y.Oq5O7q0Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5Q5');

-- Insert sample medicines
INSERT INTO medicines (name, batch_no, price, quantity, expiry_date) VALUES
('Paracetamol', 'BATCH001', 10.50, 100, '2024-12-31'),
('Amoxicillin', 'BATCH002', 25.75, 50, '2024-10-15'),
('Ibuprofen', 'BATCH003', 15.25, 75, '2024-11-30'),
('Vitamin C', 'BATCH004', 8.99, 200, '2024-09-30'); 