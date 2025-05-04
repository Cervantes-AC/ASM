-- STEP 2: Drop tables in reverse dependency order
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS fines;
DROP TABLE IF EXISTS borrow_requests;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS users;

-- STEP 3: Create tables in correct order

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'member') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(100) NOT NULL,
    asset_description TEXT,
    serial_number VARCHAR(100) UNIQUE,
    status ENUM('available', 'borrowed', 'reserved', 'missing', 'damaged') DEFAULT 'available',
    date_acquired DATE,
    value DECIMAL(10, 2),
    location VARCHAR(100),
    image_path VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE borrow_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    date_borrowed DATE,
    due_date DATE,
    date_returned DATE,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE fines (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reason TEXT,
    date_issued DATE,
    is_paid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (request_id) REFERENCES borrow_requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT,
    type ENUM('info', 'warning', 'overdue', 'reminder') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100),
    description TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- STEP 4: Sample inserts (AFTER all tables are created)

INSERT INTO users (username, password_hash, full_name, email, role) VALUES
('admin01', SHA2('adminpass', 256), 'Admin User', 'admin@example.com', 'admin'),
('member01', SHA2('memberpass', 256), 'Earl Vinent N. Menguez', 'earl@example.com', 'member');

INSERT INTO assets (asset_name, asset_description, serial_number, status, date_acquired, value, location) VALUES
('Projector', 'Epson multimedia projector', 'SN-PRJ-001', 'available', '2023-01-15', 25000.00, 'Office A'),
('Laptop', 'HP ProBook for staff use', 'SN-LPT-002', 'available', '2022-08-10', 45000.00, 'Office B');

INSERT INTO borrow_requests (user_id, asset_id, date_borrowed, due_date, status, notes)
VALUES (2, 1, '2025-04-01', '2025-04-05', 'borrowed', 'Needed for student presentation');

INSERT INTO fines (request_id, user_id, amount, reason, date_issued)
VALUES (1, 2, 500.00, 'Late return of projector', '2025-04-10');

INSERT INTO notifications (user_id, message, type)
VALUES (2, 'Your borrowed asset "Projector" is overdue.', 'overdue');

INSERT INTO logs (user_id, action, description)
VALUES (1, 'Add Asset', 'Admin added new asset: Laptop');