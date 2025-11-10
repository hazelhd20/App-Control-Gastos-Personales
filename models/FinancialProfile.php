<?php
/**
 * Financial Profile Model
 */

class FinancialProfile {
    private $conn;
    private $table = 'financial_profiles';

    public $id;
    public $user_id;
    public $monthly_income;
    public $currency;
    public $start_date;
    public $payment_methods;
    public $financial_goal;
    public $goal_description;
    public $savings_goal;
    public $savings_deadline;
    public $debt_amount;
    public $debt_deadline;
    public $monthly_payment;
    public $debt_count;
    public $spending_limit;
    public $is_initial_setup_complete;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create financial profile
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id,
                      monthly_income = :monthly_income,
                      currency = :currency,
                      start_date = :start_date,
                      payment_methods = :payment_methods,
                      financial_goal = :financial_goal,
                      goal_description = :goal_description,
                      savings_goal = :savings_goal,
                      savings_deadline = :savings_deadline,
                      debt_amount = :debt_amount,
                      debt_deadline = :debt_deadline,
                      monthly_payment = :monthly_payment,
                      debt_count = :debt_count,
                      spending_limit = :spending_limit,
                      is_initial_setup_complete = 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':monthly_income', $this->monthly_income);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':payment_methods', $this->payment_methods);
        $stmt->bindParam(':financial_goal', $this->financial_goal);
        $stmt->bindParam(':goal_description', $this->goal_description);
        $stmt->bindParam(':savings_goal', $this->savings_goal);
        $stmt->bindParam(':savings_deadline', $this->savings_deadline);
        $stmt->bindParam(':debt_amount', $this->debt_amount);
        $stmt->bindParam(':debt_deadline', $this->debt_deadline);
        $stmt->bindParam(':monthly_payment', $this->monthly_payment);
        $stmt->bindParam(':debt_count', $this->debt_count);
        $stmt->bindParam(':spending_limit', $this->spending_limit);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Get profile by user ID
     */
    public function getByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $row['payment_methods'] = json_decode($row['payment_methods'], true);
            return $row;
        }
        return false;
    }

    /**
     * Check if initial setup is complete
     */
    public function isSetupComplete($user_id) {
        $query = "SELECT is_initial_setup_complete FROM " . $this->table . " 
                  WHERE user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            return (bool)$row['is_initial_setup_complete'];
        }
        return false;
    }

    /**
     * Update financial profile
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET monthly_income = :monthly_income,
                      currency = :currency,
                      payment_methods = :payment_methods,
                      financial_goal = :financial_goal,
                      goal_description = :goal_description,
                      savings_goal = :savings_goal,
                      savings_deadline = :savings_deadline,
                      debt_amount = :debt_amount,
                      debt_deadline = :debt_deadline,
                      monthly_payment = :monthly_payment,
                      debt_count = :debt_count,
                      spending_limit = :spending_limit
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':monthly_income', $this->monthly_income);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':payment_methods', $this->payment_methods);
        $stmt->bindParam(':financial_goal', $this->financial_goal);
        $stmt->bindParam(':goal_description', $this->goal_description);
        $stmt->bindParam(':savings_goal', $this->savings_goal);
        $stmt->bindParam(':savings_deadline', $this->savings_deadline);
        $stmt->bindParam(':debt_amount', $this->debt_amount);
        $stmt->bindParam(':debt_deadline', $this->debt_deadline);
        $stmt->bindParam(':monthly_payment', $this->monthly_payment);
        $stmt->bindParam(':debt_count', $this->debt_count);
        $stmt->bindParam(':spending_limit', $this->spending_limit);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    /**
     * Update monthly income (for additional income)
     */
    public function updateIncome($user_id, $additional_income) {
        $query = "UPDATE " . $this->table . " 
                  SET monthly_income = monthly_income + :additional_income
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':additional_income', $additional_income);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Calculate recommended spending limit based on goal
     */
    public function calculateSpendingLimit($monthly_income, $financial_goal, $savings_goal = 0, $debt_amount = 0, $savings_deadline = null, $debt_deadline = null, $monthly_payment = null) {
        $limit = $monthly_income;

        if ($financial_goal === 'ahorrar' && $savings_goal > 0) {
            // If deadline is provided, calculate required monthly savings
            if (!empty($savings_deadline)) {
                $deadline = new DateTime($savings_deadline);
                $today = new DateTime();
                $months = max(1, (int)$today->diff($deadline)->format('%m') + ($today->diff($deadline)->format('%y') * 12));
                
                // Calculate required monthly savings
                $required_monthly_savings = $savings_goal / $months;
                
                // Ensure we don't require more than 50% of income for savings
                $max_savings = $monthly_income * 0.50;
                $monthly_savings = min($required_monthly_savings, $max_savings);
                
                $limit = $monthly_income - $monthly_savings;
            } else {
                // Default: recommend saving 20-30% of income
                $recommended_savings = $monthly_income * 0.25;
                $limit = $monthly_income - $recommended_savings;
            }
        } elseif ($financial_goal === 'pagar_deudas' && $debt_amount > 0) {
            // If user specified monthly payment, use it
            if (!empty($monthly_payment) && $monthly_payment > 0) {
                $recommended_payment = min($monthly_payment, $monthly_income * 0.50);
                $limit = $monthly_income - $recommended_payment;
            } elseif (!empty($debt_deadline)) {
                // If deadline is provided, calculate required monthly payment
                $deadline = new DateTime($debt_deadline);
                $today = new DateTime();
                $months = max(1, (int)$today->diff($deadline)->format('%m') + ($today->diff($deadline)->format('%y') * 12));
                
                // Calculate required monthly payment
                $required_monthly_payment = $debt_amount / $months;
                
                // Ensure payment doesn't exceed 50% of income
                $max_payment = $monthly_income * 0.50;
                $recommended_payment = min($required_monthly_payment, $max_payment);
                
                $limit = $monthly_income - $recommended_payment;
            } else {
                // Default: recommend paying 30% towards debt, but at least enough to pay in 24 months
                $min_monthly_payment = $debt_amount / 24;
                $recommended_payment = max($monthly_income * 0.30, $min_monthly_payment);
                // Ensure payment doesn't exceed 50% of income
                $recommended_payment = min($recommended_payment, $monthly_income * 0.50);
                $limit = $monthly_income - $recommended_payment;
            }
        } else {
            // For general control, recommend 80% spending limit
            $limit = $monthly_income * 0.80;
        }

        // Ensure limit is at least 50% of income (safety margin)
        $min_limit = $monthly_income * 0.50;
        $limit = max($limit, $min_limit);

        return round($limit, 2);
    }

    /**
     * Validate financial goal feasibility
     * Returns array with 'valid' boolean and 'message' string
     */
    public function validateGoalFeasibility($monthly_income, $financial_goal, $savings_goal = 0, $savings_deadline = null, $debt_amount = 0, $debt_deadline = null, $monthly_payment = null) {
        $result = ['valid' => true, 'message' => '', 'warnings' => []];

        if ($financial_goal === 'ahorrar') {
            if ($savings_goal <= 0) {
                $result['valid'] = false;
                $result['message'] = 'La meta de ahorro debe ser mayor a 0';
                return $result;
            }

            if (!empty($savings_deadline)) {
                $deadline = new DateTime($savings_deadline);
                $today = new DateTime();
                
                if ($deadline <= $today) {
                    $result['valid'] = false;
                    $result['message'] = 'La fecha límite debe ser una fecha futura';
                    return $result;
                }

                // Calculate months until deadline
                $months = max(1, (int)$today->diff($deadline)->format('%m') + ($today->diff($deadline)->format('%y') * 12));
                
                // Calculate required monthly savings
                $required_monthly_savings = $savings_goal / $months;
                
                // Check if it's feasible (shouldn't require more than 50% of income)
                if ($required_monthly_savings > $monthly_income * 0.50) {
                    $result['warnings'][] = sprintf(
                        'Para alcanzar tu meta de %s en %d meses, necesitarías ahorrar %s mensualmente (%.1f%% de tu ingreso). Esto puede ser difícil de mantener.',
                        number_format($savings_goal, 2),
                        $months,
                        number_format($required_monthly_savings, 2),
                        ($required_monthly_savings / $monthly_income) * 100
                    );
                }

                // Check if deadline is too far (more than 10 years)
                if ($months > 120) {
                    $result['warnings'][] = 'La fecha límite es muy lejana. Considera establecer una meta más cercana para mantener la motivación.';
                }
            }
        } elseif ($financial_goal === 'pagar_deudas') {
            if ($debt_amount <= 0) {
                $result['valid'] = false;
                $result['message'] = 'El monto de la deuda debe ser mayor a 0';
                return $result;
            }

            // Validate debt deadline if provided
            if (!empty($debt_deadline)) {
                $deadline = new DateTime($debt_deadline);
                $today = new DateTime();
                
                if ($deadline <= $today) {
                    $result['valid'] = false;
                    $result['message'] = 'La fecha objetivo para pagar deudas debe ser una fecha futura';
                    return $result;
                }

                // Calculate months until deadline
                $months = max(1, (int)$today->diff($deadline)->format('%m') + ($today->diff($deadline)->format('%y') * 12));
                
                // Calculate required monthly payment
                $required_monthly_payment = $debt_amount / $months;
                
                // Check if it's feasible
                if ($required_monthly_payment > $monthly_income * 0.50) {
                    $result['warnings'][] = sprintf(
                        'Para pagar tu deuda de %s en %d meses, necesitarías pagar %s mensualmente (%.1f%% de tu ingreso). Esto puede ser difícil de mantener.',
                        number_format($debt_amount, 2),
                        $months,
                        number_format($required_monthly_payment, 2),
                        ($required_monthly_payment / $monthly_income) * 100
                    );
                }

                // Check if deadline is too far (more than 10 years)
                if ($months > 120) {
                    $result['warnings'][] = 'La fecha objetivo es muy lejana. Considera establecer una fecha más cercana para reducir intereses.';
                }
            }

            // Validate monthly payment if provided
            if (!empty($monthly_payment) && $monthly_payment > 0) {
                if ($monthly_payment > $monthly_income * 0.50) {
                    $result['warnings'][] = sprintf(
                        'El pago mensual de %s representa %.1f%% de tu ingreso. Asegúrate de que esto sea sostenible.',
                        number_format($monthly_payment, 2),
                        ($monthly_payment / $monthly_income) * 100
                    );
                }

                // Check if monthly payment is sufficient to pay debt in reasonable time
                if ($monthly_payment > 0) {
                    $months_to_pay = ceil($debt_amount / $monthly_payment);
                    if ($months_to_pay > 120) {
                        $result['warnings'][] = sprintf(
                            'Con un pago mensual de %s, tardarías aproximadamente %d meses (%.1f años) en pagar tu deuda. Considera aumentar el pago mensual.',
                            number_format($monthly_payment, 2),
                            $months_to_pay,
                            $months_to_pay / 12
                        );
                    }
                }
            }

            // Check if debt is reasonable compared to income
            $debt_to_income_ratio = $debt_amount / ($monthly_income * 12); // Annual income
            
            if ($debt_to_income_ratio > 5) {
                $result['warnings'][] = 'Tu deuda es muy alta comparada con tu ingreso anual. Considera buscar asesoría financiera profesional.';
            }

            // Calculate minimum payment to pay in 24 months (if no deadline or payment specified)
            if (empty($debt_deadline) && empty($monthly_payment)) {
                $min_monthly_payment = $debt_amount / 24;
                if ($min_monthly_payment > $monthly_income * 0.50) {
                    $result['warnings'][] = sprintf(
                        'Para pagar tu deuda en 24 meses, necesitarías pagar %s mensualmente (%.1f%% de tu ingreso). Esto puede ser difícil de mantener.',
                        number_format($min_monthly_payment, 2),
                        ($min_monthly_payment / $monthly_income) * 100
                    );
                }
            }
        } elseif ($financial_goal === 'otro') {
            // Validation for "otro" will be done in controller (description required)
        }

        return $result;
    }

    /**
     * Validate spending limit
     * Returns array with 'valid' boolean and 'message' string
     */
    public function validateSpendingLimit($spending_limit, $monthly_income, $financial_goal, $savings_goal = 0, $debt_amount = 0) {
        $result = ['valid' => true, 'message' => '', 'warnings' => []];

        // Basic validations
        if ($spending_limit <= 0) {
            $result['valid'] = false;
            $result['message'] = 'El límite de gasto debe ser mayor a 0';
            return $result;
        }

        if ($spending_limit > $monthly_income) {
            $result['valid'] = false;
            $result['message'] = 'El límite de gasto no puede ser mayor que tu ingreso mensual';
            return $result;
        }

        // Check if spending limit is too high relative to goal
        $spending_percentage = ($spending_limit / $monthly_income) * 100;

        if ($financial_goal === 'ahorrar' && $savings_goal > 0) {
            $available_for_savings = $monthly_income - $spending_limit;
            $recommended_savings = $monthly_income * 0.20; // At least 20%
            
            if ($available_for_savings < $recommended_savings) {
                $result['warnings'][] = sprintf(
                    'Con este límite de gasto, solo quedarían %s para ahorro (%.1f%% de tu ingreso). Se recomienda ahorrar al menos 20%% para alcanzar tu meta.',
                    number_format($available_for_savings, 2),
                    ($available_for_savings / $monthly_income) * 100
                );
            }
        } elseif ($financial_goal === 'pagar_deudas' && $debt_amount > 0) {
            $available_for_debt = $monthly_income - $spending_limit;
            $recommended_payment = $monthly_income * 0.30; // At least 30%
            
            if ($available_for_debt < $recommended_payment) {
                $result['warnings'][] = sprintf(
                    'Con este límite de gasto, solo quedarían %s para pagar deudas (%.1f%% de tu ingreso). Se recomienda destinar al menos 30%% para pagar deudas.',
                    number_format($available_for_debt, 2),
                    ($available_for_debt / $monthly_income) * 100
                );
            }
        }

        // Warning if spending limit is too high (more than 90% of income)
        if ($spending_percentage > 90) {
            $result['warnings'][] = 'El límite de gasto es muy alto. Se recomienda dejar al menos 10% de margen para imprevistos.';
        }

        return $result;
    }

    /**
     * Get spending limit for user
     */
    public function getSpendingLimit($user_id) {
        $query = "SELECT spending_limit FROM " . $this->table . " 
                  WHERE user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            return $row['spending_limit'];
        }
        return 0;
    }
}

