-- Migration: Update icons from emojis to Font Awesome icons
-- Date: 2024
-- This migration updates existing categories and adds more default categories

-- Step 1: Update existing expense categories with emojis to Font Awesome icons
UPDATE categories 
SET icon = 'fa-utensils', color = '#FF6B6B'
WHERE user_id IS NULL AND name = 'Alimentación' AND icon = '🍔';

UPDATE categories 
SET icon = 'fa-car', color = '#4ECDC4'
WHERE user_id IS NULL AND name = 'Transporte' AND icon = '🚗';

UPDATE categories 
SET icon = 'fa-gamepad', color = '#95E1D3'
WHERE user_id IS NULL AND name = 'Entretenimiento' AND icon = '🎮';

UPDATE categories 
SET icon = 'fa-home', color = '#F38181'
WHERE user_id IS NULL AND name = 'Vivienda' AND icon = '🏠';

UPDATE categories 
SET icon = 'fa-pills', color = '#AA96DA'
WHERE user_id IS NULL AND name = 'Salud' AND icon = '💊';

UPDATE categories 
SET icon = 'fa-book', color = '#FCBAD3'
WHERE user_id IS NULL AND name = 'Educación' AND icon = '📚';

UPDATE categories 
SET icon = 'fa-tshirt', color = '#A8D8EA'
WHERE user_id IS NULL AND name = 'Ropa' AND icon = '👔';

UPDATE categories 
SET icon = 'fa-lightbulb', color = '#FFAAA5'
WHERE user_id IS NULL AND name = 'Servicios' AND icon = '💡';

UPDATE categories 
SET icon = 'fa-box', color = '#C7CEEA'
WHERE user_id IS NULL AND name = 'Otros' AND type = 'expense' AND (icon = '📦' OR icon IS NULL);

-- Step 2: Update existing income categories with emojis to Font Awesome icons
UPDATE categories 
SET icon = 'fa-briefcase', color = '#10B981'
WHERE user_id IS NULL AND name = 'Salario' AND icon = '💼';

UPDATE categories 
SET icon = 'fa-laptop-code', color = '#3B82F6'
WHERE user_id IS NULL AND name = 'Freelance' AND icon = '💻';

UPDATE categories 
SET icon = 'fa-chart-line', color = '#8B5CF6'
WHERE user_id IS NULL AND name = 'Inversiones' AND icon = '📈';

UPDATE categories 
SET icon = 'fa-wallet', color = '#F59E0B'
WHERE user_id IS NULL AND name = 'Venta' AND icon = '💰';

UPDATE categories 
SET icon = 'fa-gift', color = '#EC4899'
WHERE user_id IS NULL AND name = 'Regalo' AND icon = '🎁';

UPDATE categories 
SET icon = 'fa-dollar-sign', color = '#14B8A6'
WHERE user_id IS NULL AND name = 'Otros' AND type = 'income' AND (icon = '💵' OR icon IS NULL);

-- Step 3: Delete existing default categories and insert new ones with Font Awesome icons
-- This ensures we have a clean set of default categories
DELETE FROM categories WHERE user_id IS NULL;

-- Insert default expense categories with Font Awesome icons
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

-- Insert default income categories with Font Awesome icons
INSERT INTO categories (user_id, name, type, icon, color) VALUES
(NULL, 'Salario', 'income', 'fa-briefcase', '#10B981'),
(NULL, 'Freelance', 'income', 'fa-laptop-code', '#3B82F6'),
(NULL, 'Inversiones', 'income', 'fa-chart-line', '#8B5CF6'),
(NULL, 'Venta', 'income', 'fa-wallet', '#F59E0B'),
(NULL, 'Regalo', 'income', 'fa-gift', '#EC4899'),
(NULL, 'Bonificación', 'income', 'fa-trophy', '#EC4899'),
(NULL, 'Préstamo', 'income', 'fa-handshake', '#14B8A6'),
(NULL, 'Otros', 'income', 'fa-dollar-sign', '#14B8A6');

-- Step 4: Update any user-created categories that still have emojis
-- Map common emojis to Font Awesome icons
-- Note: This will update categories that match specific emojis
UPDATE categories 
SET icon = CASE 
    WHEN icon = '🍔' THEN 'fa-utensils'
    WHEN icon = '🚗' THEN 'fa-car'
    WHEN icon = '🏠' THEN 'fa-home'
    WHEN icon = '💊' THEN 'fa-pills'
    WHEN icon = '📚' THEN 'fa-book'
    WHEN icon = '👔' THEN 'fa-tshirt'
    WHEN icon = '💡' THEN 'fa-lightbulb'
    WHEN icon = '🍕' THEN 'fa-pizza-slice'
    WHEN icon = '🍺' THEN 'fa-beer'
    WHEN icon = '🎬' THEN 'fa-film'
    WHEN icon = '🎮' THEN 'fa-gamepad'
    WHEN icon = '⚽' THEN 'fa-futbol'
    WHEN icon = '🎨' THEN 'fa-palette'
    WHEN icon = '🛍️' THEN 'fa-shopping-bag'
    WHEN icon = '🧴' THEN 'fa-spray-can'
    WHEN icon = '🧼' THEN 'fa-soap'
    WHEN icon = '💰' THEN 'fa-wallet'
    WHEN icon = '💵' THEN 'fa-dollar-sign'
    WHEN icon = '🎁' THEN 'fa-gift'
    WHEN icon = '📦' THEN 'fa-box'
    WHEN icon = '🚇' THEN 'fa-subway'
    WHEN icon = '✈️' THEN 'fa-plane'
    WHEN icon = '🏦' THEN 'fa-university'
    WHEN icon = '🏥' THEN 'fa-hospital'
    WHEN icon = '📱' THEN 'fa-mobile-alt'
    WHEN icon = '💻' THEN 'fa-laptop'
    WHEN icon = '🖥️' THEN 'fa-desktop'
    WHEN icon = '📺' THEN 'fa-tv'
    WHEN icon = '🎵' THEN 'fa-music'
    WHEN icon = '📷' THEN 'fa-camera'
    WHEN icon = '💼' THEN 'fa-briefcase'
    WHEN icon = '📈' THEN 'fa-chart-line'
    WHEN icon = '💳' THEN 'fa-credit-card'
    WHEN icon = '🤝' THEN 'fa-handshake'
    WHEN icon = '🎓' THEN 'fa-graduation-cap'
    WHEN icon = '🏆' THEN 'fa-trophy'
    WHEN icon = '⭐' THEN 'fa-star'
    WHEN icon = '🎉' THEN 'fa-birthday-cake'
    WHEN icon = '🚀' THEN 'fa-rocket'
    WHEN icon = '🔔' THEN 'fa-bell'
    WHEN icon = '🎯' THEN 'fa-bullseye'
    WHEN icon = '🌟' THEN 'fa-star'
    WHEN icon = '✨' THEN 'fa-magic'
    ELSE icon
END
WHERE user_id IS NOT NULL 
  AND icon NOT LIKE 'fa-%'
  AND icon IN ('🍔', '🚗', '🏠', '💊', '📚', '👔', '💡', '🍕', '🍺', '🎬', 
               '🎮', '⚽', '🎨', '🛍️', '🧴', '🧼', '💰', '💵', '🎁', '📦', 
               '🚇', '✈️', '🏦', '🏥', '📱', '💻', '🖥️', '📺', '🎵', '📷', 
               '💼', '📈', '💳', '🤝', '🎓', '🏆', '⭐', '🎉', '🚀', '🔔', 
               '🎯', '🌟', '✨');

