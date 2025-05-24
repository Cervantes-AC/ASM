-- DROP TABLES IF EXIST TO AVOID CONFLICTS DURING TESTING
DROP TABLE IF EXISTS notifications, logs, fines, reservations, returns, borrow_requests, assets, users;

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

-- BORROW REQUESTS TABLE
CREATE TABLE borrow_requests (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    quantity INT,
    date_borrowed DATETIME,
    expected_return DATETIME,
    status ENUM('pending', 'approved', 'denied', 'returned', 'overdue') DEFAULT 'pending',
    remarks TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
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

-- BORROW REQUESTS
INSERT INTO borrow_requests (user_id, asset_id, quantity, date_borrowed, expected_return, status)
VALUES 
(3, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'approved');

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
