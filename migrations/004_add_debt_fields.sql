-- Migration: Add debt management fields
-- Description: Adds fields for better debt tracking: deadline, monthly payment, and debt count
-- Date: 2024

USE control_gastos;

-- Add new debt-related fields to financial_profiles table
ALTER TABLE financial_profiles
ADD COLUMN debt_deadline DATE NULL AFTER debt_amount,
ADD COLUMN monthly_payment DECIMAL(10, 2) NULL AFTER debt_deadline,
ADD COLUMN debt_count INT NULL AFTER monthly_payment;

-- Add comment to explain new fields
ALTER TABLE financial_profiles 
MODIFY COLUMN debt_deadline DATE NULL COMMENT 'Fecha objetivo para pagar todas las deudas',
MODIFY COLUMN monthly_payment DECIMAL(10, 2) NULL COMMENT 'Pago mensual mínimo o recomendado para deudas',
MODIFY COLUMN debt_count INT NULL COMMENT 'Número total de deudas';

