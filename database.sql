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
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(64) NULL,
    verification_token_expiry DATETIME NULL,
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

-- Table: categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(50) NOT NULL,
    type ENUM('expense', 'income') NOT NULL DEFAULT 'expense',
    icon VARCHAR(50) NULL,
    color VARCHAR(7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, name, type),
    INDEX idx_user_type (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default expense categories (system-wide defaults) with Font Awesome icons
INSERT INTO categories (user_id, name, type, icon, color) VALUES
(NULL, 'Alimentación', 'expense', 'fa-utensils', '#FF6B6B'),
(NULL, 'Transporte', 'expense', 'fa-car', '#4ECDC4'),
(NULL, 'Entretenimiento', 'expense', 'fa-gamepad', '#95E1D3'),
(NULL, 'Vivienda', 'expense', 'fa-home', '#F38181'),
(NULL, 'Salud', 'expense', 'fa-pills', '#AA96DA'),
(NULL, 'Educación', 'expense', 'fa-book', '#FCBAD3'),
(NULL, 'Ropa', 'expense', 'fa-tshirt', '#A8D8EA'),
(NULL, 'Servicios', 'expense', 'fa-lightbulb', '#FFAAA5'),
(NULL, 'Compras', 'expense', 'fa-shopping-bag', '#EF4444'),
(NULL, 'Restaurantes', 'expense', 'fa-pizza-slice', '#6366F1'),
(NULL, 'Deportes', 'expense', 'fa-futbol', '#84CC16'),
(NULL, 'Tecnología', 'expense', 'fa-laptop', '#F97316'),
(NULL, 'Viajes', 'expense', 'fa-plane', '#06B6D4'),
(NULL, 'Bancos', 'expense', 'fa-university', '#3B82F6'),
(NULL, 'Otros', 'expense', 'fa-box', '#C7CEEA');

-- Insert default income categories (system-wide defaults) with Font Awesome icons
INSERT INTO categories (user_id, name, type, icon, color) VALUES
(NULL, 'Salario', 'income', 'fa-briefcase', '#10B981'),
(NULL, 'Freelance', 'income', 'fa-laptop-code', '#3B82F6'),
(NULL, 'Inversiones', 'income', 'fa-chart-line', '#8B5CF6'),
(NULL, 'Venta', 'income', 'fa-wallet', '#F59E0B'),
(NULL, 'Regalo', 'income', 'fa-gift', '#EC4899'),
(NULL, 'Bonificación', 'income', 'fa-trophy', '#EC4899'),
(NULL, 'Préstamo', 'income', 'fa-handshake', '#14B8A6'),
(NULL, 'Otros', 'income', 'fa-dollar-sign', '#14B8A6');

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

-- Table: monthly_goal_progress (for tracking monthly progress towards financial goals)
CREATE TABLE IF NOT EXISTS monthly_goal_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    goal_type VARCHAR(50) NOT NULL, -- 'savings', 'debt_payment', 'spending_control'
    planned_amount DECIMAL(10, 2) NOT NULL, -- Monto planificado para el mes (calculado del objetivo inicial)
    actual_amount DECIMAL(10, 2) DEFAULT 0, -- Monto real acumulado del mes (ingresos - gastos)
    adjustments DECIMAL(10, 2) DEFAULT 0, -- Ajustes manuales (retiros, gastos excepcionales que afectan el objetivo)
    status VARCHAR(20) DEFAULT 'in_progress', -- 'in_progress', 'completed', 'adjusted', 'pending'
    notes TEXT NULL, -- Notas sobre ajustes o situaciones especiales
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month_goal (user_id, year, month, goal_type),
    INDEX idx_user_year_month (user_id, year, month),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

