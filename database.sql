-- Create Database
CREATE DATABASE IF NOT EXISTS tour_travel_db;

USE tour_travel_db;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example Insert (Optional)
-- INSERT INTO users (name, email, password) VALUES ('John Doe', 'john@example.com', 'password123');