-- Migration: Add monthly_goal_progress table
-- Date: 2024
-- Description: Adds table to track monthly progress towards financial goals

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

-- Note: This migration adds the monthly_goal_progress table to track monthly progress
-- towards financial goals. The system will automatically initialize monthly goals
-- when users access their dashboard.

