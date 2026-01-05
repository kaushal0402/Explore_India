-- Create ratings table for tour package ratings
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    user_ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_package_state (package_name, state),
    INDEX idx_state (state),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: Create a view for average ratings
CREATE OR REPLACE VIEW package_ratings_summary AS
SELECT 
    package_name,
    state,
    AVG(rating) as average_rating,
    COUNT(*) as total_ratings
FROM ratings
GROUP BY package_name, state;