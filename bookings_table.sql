-- Add Bookings Table to track tour bookings and payments

USE tour_travel_db;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    state VARCHAR(50) NOT NULL,
    package_name VARCHAR(100) NOT NULL,
    package_price DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (customer_email),
    INDEX idx_booking_date (booking_date)
);