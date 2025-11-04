-- Migration: Rename expense_categories to categories
-- Date: 2024
-- This migration renames the table to better reflect that it handles both expense and income categories

-- Rename the table
RENAME TABLE expense_categories TO categories;

