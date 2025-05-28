-- DROP TABLES IF EXIST TO AVOID CONFLICTS DURING TESTING
DROP TABLE IF EXISTS notifications, logs, fines, reservations, returns, borrow_requests, asset_items, assets, users;

-- USERS TABLE
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'member') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ASSETS TABLE
CREATE TABLE assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    serial_code VARCHAR(50),
    quantity INT NOT NULL,
    `condition` VARCHAR(100),
    status ENUM('available', 'borrowed', 'reserved', 'damaged', 'lost') DEFAULT 'available',
    location VARCHAR(100),
    image_path VARCHAR(255),
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ASSET ITEMS TABLE - Track individual items within assets
CREATE TABLE asset_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    item_number INT NOT NULL,
    `condition` VARCHAR(100) DEFAULT 'Good',
    status ENUM('available', 'borrowed', 'maintenance', 'damaged') DEFAULT 'available',
    borrowed_by INT NULL,
    borrowed_date DATETIME NULL,
    return_date DATETIME NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Ensure unique combination of asset_id and item_number
    UNIQUE KEY unique_asset_item (asset_id, item_number),
    
    -- Index for better performance
    INDEX idx_asset_status (asset_id, status),
    INDEX idx_borrowed_by (borrowed_by),
    
    -- Foreign key constraints
    CONSTRAINT fk_asset_items_asset FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE,
    CONSTRAINT fk_asset_items_user FOREIGN KEY (borrowed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- BORROW REQUESTS TABLE (CONSOLIDATED VERSION)
CREATE TABLE borrow_requests (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    item_id INT NULL,
    item_number INT NULL,
    quantity INT DEFAULT 1,
    borrow_condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged') NULL,
    return_condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged') NULL,
    return_notes TEXT NULL,
    request_date DATETIME NOT NULL,
    date_borrowed DATETIME NULL,
    expected_return DATETIME NULL,
    return_date DATETIME NULL,
    status ENUM('pending', 'approved', 'rejected', 'denied', 'returned', 'overdue') DEFAULT 'pending',
    notes TEXT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES asset_items(item_id) ON DELETE SET NULL
);

-- RETURNS TABLE
CREATE TABLE returns (
    return_id INT AUTO_INCREMENT PRIMARY KEY,
    borrow_id INT NOT NULL,
    return_date DATETIME,
    `condition` VARCHAR(100),
    remarks TEXT,
    FOREIGN KEY (borrow_id) REFERENCES borrow_requests(borrow_id) ON DELETE CASCADE
);

-- RESERVATIONS TABLE
CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    reserved_from DATETIME,
    reserved_to DATETIME,
    status ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
);

-- FINES TABLE
CREATE TABLE fines (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    amount DECIMAL(10,2),
    reason VARCHAR(255),
    status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    date_issued DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
);

-- LOGS TABLE
CREATE TABLE logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100),
    target_id INT,
    description TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- NOTIFICATIONS TABLE
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT,
    type ENUM('reminder', 'alert', 'info') DEFAULT 'info',
    status ENUM('unread', 'read') DEFAULT 'unread',
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ASSET OVERVIEW VIEW - Easy asset overview with item counts
CREATE OR REPLACE VIEW asset_overview AS
SELECT 
    a.asset_id,
    a.asset_name,
    a.category,
    a.serial_code,
    a.quantity,
    a.`condition` as main_condition,
    a.status,
    a.location,
    a.date_added,
    COUNT(ai.item_id) as tracked_items,
    SUM(CASE WHEN ai.status = 'available' THEN 1 ELSE 0 END) as available_items,
    SUM(CASE WHEN ai.status = 'borrowed' THEN 1 ELSE 0 END) as borrowed_items,
    SUM(CASE WHEN ai.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_items,
    SUM(CASE WHEN ai.status = 'damaged' THEN 1 ELSE 0 END) as damaged_items
FROM assets a
LEFT JOIN asset_items ai ON a.asset_id = ai.asset_id
GROUP BY a.asset_id, a.asset_name, a.category, a.serial_code, a.quantity, a.`condition`, a.status, a.location, a.date_added;

-- SAMPLE INSERTS

-- USERS
INSERT INTO users (full_name, email, password, role)
VALUES 
('Admin User', 'admin@cmu.edu.ph', 'hashedpass1', 'admin'),
('Staff Jane', 'jane@cmu.edu.ph', 'hashedpass2', 'staff'),
('Member John', 'john@cmu.edu.ph', 'hashedpass3', 'member');

-- ASSETS
INSERT INTO assets (asset_name, category, serial_code, quantity, `condition`, location)
VALUES 
('Projector', 'Electronics', 'PRJ-001', 2, 'Good', 'Room A101'),
('Microphone', 'Audio', 'MIC-005', 5, 'Excellent', 'Room B202'),
('Laptop', 'Computer', 'LTP-003', 3, 'Fair', 'Room C303');

-- ASSET ITEMS (Sample individual items for tracking)
INSERT INTO asset_items (asset_id, item_number, `condition`, status) VALUES
-- Projector items (asset_id = 1, quantity = 2)
(1, 1, 'Good', 'borrowed'),    -- One projector is borrowed
(1, 2, 'Good', 'available'),   -- One projector is available

-- Microphone items (asset_id = 2, quantity = 5)
(2, 1, 'Excellent', 'available'),
(2, 2, 'Excellent', 'available'),
(2, 3, 'Excellent', 'available'),
(2, 4, 'Excellent', 'available'),
(2, 5, 'Excellent', 'available'),

-- Laptop items (asset_id = 3, quantity = 3)
(3, 1, 'Fair', 'available'),
(3, 2, 'Fair', 'available'),
(3, 3, 'Fair', 'maintenance');  -- One laptop is in maintenance

-- BORROW REQUESTS (Updated with new columns)
INSERT INTO borrow_requests (user_id, asset_id, item_id, item_number, quantity, borrow_condition, request_date, date_borrowed, expected_return, status)
VALUES 
(3, 1, 1, 1, 1, 'good', NOW(), NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'approved');

-- Update the asset_items table to reflect the borrowed item
UPDATE asset_items 
SET status = 'borrowed', borrowed_by = 3, borrowed_date = NOW() 
WHERE asset_id = 1 AND item_number = 1;

-- RETURNS
INSERT INTO returns (borrow_id, return_date, `condition`, remarks)
VALUES 
(1, NOW(), 'Good', 'Returned on time');

-- RESERVATIONS
INSERT INTO reservations (user_id, asset_id, reserved_from, reserved_to, status)
VALUES 
(2, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY), 'pending');

-- FINES
INSERT INTO fines (user_id, asset_id, amount, reason)
VALUES 
(3, 1, 250.00, 'Lost item');

-- LOGS
INSERT INTO logs (user_id, action, target_id, description)
VALUES 
(1, 'create_asset', 1, 'Created Projector asset');

-- NOTIFICATIONS
INSERT INTO notifications (user_id, message, type)
VALUES 
(3, 'Your borrowed projector is due in 2 days.', 'reminder');