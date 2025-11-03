-- Migration: Add support for custom categories per user
-- Date: 2024
-- Manual approach for older MySQL versions

-- Step 1: Check if table exists and has the old structure
-- If running on a fresh installation, this won't be needed

-- Step 2: Add new columns one by one
ALTER TABLE expense_categories ADD COLUMN user_id INT NULL;
ALTER TABLE expense_categories ADD COLUMN type ENUM('expense', 'income') NOT NULL DEFAULT 'expense';
ALTER TABLE expense_categories ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE expense_categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Step 3: Add foreign key (will work even if constraint already exists)
-- You may need to drop and recreate if there are errors
ALTER TABLE expense_categories ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Step 4: Drop old unique constraint if exists
ALTER TABLE expense_categories DROP INDEX name;

-- Step 5: Add new unique constraint
ALTER TABLE expense_categories ADD UNIQUE KEY unique_user_category (user_id, name, type);

-- Step 6: Add index for performance
ALTER TABLE expense_categories ADD INDEX idx_user_type (user_id, type);

-- Step 7: Insert default income categories
INSERT INTO expense_categories (user_id, name, type, icon, color) VALUES
(NULL, 'Salario', 'income', '💼', '#10B981'),
(NULL, 'Freelance', 'income', '💻', '#3B82F6'),
(NULL, 'Inversiones', 'income', '📈', '#8B5CF6'),
(NULL, 'Venta', 'income', '💰', '#F59E0B'),
(NULL, 'Regalo', 'income', '🎁', '#EC4899'),
(NULL, 'Otros', 'income', '💵', '#14B8A6');

