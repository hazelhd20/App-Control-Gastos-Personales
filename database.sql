-- Database: control_gastos

CREATE DATABASE IF NOT EXISTS control_gastos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE control_gastos;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) NULL,
    reset_token_expiry DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: financial_profiles
CREATE TABLE IF NOT EXISTS financial_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    monthly_income DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'MXN',
    start_date DATE NOT NULL,
    payment_methods TEXT NOT NULL, -- JSON: ["efectivo", "tarjeta"]
    financial_goal VARCHAR(50) NOT NULL, -- ahorrar, pagar_deudas, controlar_gastos, otro
    goal_description TEXT NULL,
    savings_goal DECIMAL(10, 2) NULL,
    savings_deadline DATE NULL,
    debt_amount DECIMAL(10, 2) NULL,
    spending_limit DECIMAL(10, 2) NOT NULL,
    is_initial_setup_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    payment_method VARCHAR(20) NULL, -- efectivo, tarjeta
    description TEXT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, transaction_date),
    INDEX idx_type (type),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: expense_categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(50) NULL,
    color VARCHAR(7) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default expense categories
INSERT INTO expense_categories (name, icon, color) VALUES
('Alimentación', '🍔', '#FF6B6B'),
('Transporte', '🚗', '#4ECDC4'),
('Entretenimiento', '🎮', '#95E1D3'),
('Vivienda', '🏠', '#F38181'),
('Salud', '💊', '#AA96DA'),
('Educación', '📚', '#FCBAD3'),
('Ropa', '👔', '#A8D8EA'),
('Servicios', '💡', '#FFAAA5'),
('Otros', '📦', '#C7CEEA');

-- Table: alerts
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- limit_exceeded, limit_warning, inactivity, goal_progress
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: income_sources (for additional income tracking)
CREATE TABLE IF NOT EXISTS income_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    frequency VARCHAR(20) NOT NULL, -- monthly, biweekly, one-time
    received_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

