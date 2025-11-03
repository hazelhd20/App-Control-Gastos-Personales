-- Migration: Add support for custom categories per user
-- Date: 2024

-- First, drop the old unique index on name if it exists
ALTER TABLE expense_categories DROP INDEX name;

-- Now add the new columns
ALTER TABLE expense_categories 
ADD COLUMN user_id INT NULL AFTER id,
ADD COLUMN type ENUM('expense', 'income') NOT NULL DEFAULT 'expense' AFTER name,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER color,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add foreign key
ALTER TABLE expense_categories
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add new unique constraint for user_id, name, and type combination
ALTER TABLE expense_categories
ADD UNIQUE KEY unique_user_category (user_id, name, type);

-- Add index for query optimization
ALTER TABLE expense_categories
ADD INDEX idx_user_type (user_id, type);

-- Insert default income categories
INSERT INTO expense_categories (user_id, name, type, icon, color) VALUES
(NULL, 'Salario', 'income', '💼', '#10B981'),
(NULL, 'Freelance', 'income', '💻', '#3B82F6'),
(NULL, 'Inversiones', 'income', '📈', '#8B5CF6'),
(NULL, 'Venta', 'income', '💰', '#F59E0B'),
(NULL, 'Regalo', 'income', '🎁', '#EC4899'),
(NULL, 'Otros', 'income', '💵', '#14B8A6');

