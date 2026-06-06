-- Database initialization script
-- This script creates the necessary tables and seeds initial data

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create licenses table
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_license_key (license_key),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create invoice_info table - stores customer invoice information (current)
CREATE TABLE IF NOT EXISTS invoice_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    company_name VARCHAR(255) NOT NULL COMMENT '发票抬头',
    tax_id VARCHAR(50) NOT NULL COMMENT '税号',
    invoice_email VARCHAR(255) NOT NULL COMMENT '开票邮箱',
    invoice_preference ENUM('special', 'general', 'electronic') DEFAULT 'electronic' COMMENT '开票偏好：专票/普票/电子票',
    address VARCHAR(500) NULL COMMENT '地址（专票需要）',
    phone VARCHAR(50) NULL COMMENT '电话（专票需要）',
    bank_name VARCHAR(255) NULL COMMENT '开户银行（专票需要）',
    bank_account VARCHAR(100) NULL COMMENT '银行账号（专票需要）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create orders table - stores purchase/renewal orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_id INT NULL,
    order_no VARCHAR(50) NOT NULL UNIQUE,
    order_type ENUM('purchase', 'renewal', 'additional') NOT NULL COMMENT '订单类型：新购/续费/增购',
    product_name VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    invoice_generated TINYINT(1) DEFAULT 0 COMMENT '是否已生成开票申请',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_order_no (order_no),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create invoice_applications table - stores invoice requests
CREATE TABLE IF NOT EXISTS invoice_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'issued') DEFAULT 'pending' COMMENT '待审核/已通过/已驳回/已开票',
    rejection_reason TEXT NULL COMMENT '驳回原因，说明缺什么资料',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create invoices table - stores issued invoices (immutable snapshot)
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_application_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE COMMENT '发票号码',
    amount DECIMAL(10, 2) NOT NULL,
    company_name VARCHAR(255) NOT NULL COMMENT '快照：发票抬头',
    tax_id VARCHAR(50) NOT NULL COMMENT '快照：税号',
    invoice_email VARCHAR(255) NOT NULL COMMENT '快照：开票邮箱',
    invoice_preference ENUM('special', 'general', 'electronic') NOT NULL COMMENT '快照：开票偏好',
    address VARCHAR(500) NULL COMMENT '快照：地址',
    phone VARCHAR(50) NULL COMMENT '快照：电话',
    bank_name VARCHAR(255) NULL COMMENT '快照：开户银行',
    bank_account VARCHAR(100) NULL COMMENT '快照：银行账号',
    invoice_type ENUM('special', 'general', 'electronic') NOT NULL,
    issued_by INT NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_application_id) REFERENCES invoice_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_user_id (user_id),
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: User seeding is handled by PHP script (app/scripts/seed_users.php)
-- This ensures correct password hashing. Users will be created on first container startup.
-- Sample licenses will be created by app/scripts/seed_licenses.php after users are created.
