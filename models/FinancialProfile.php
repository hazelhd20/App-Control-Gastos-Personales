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
     * Calculate recommended spending limit
     */
    public function calculateSpendingLimit($monthly_income, $financial_goal, $savings_goal = 0, $debt_amount = 0) {
        $limit = $monthly_income;

        if ($financial_goal === 'ahorrar' && $savings_goal > 0) {
            // Recommend saving 20-30% of income
            $recommended_savings = $monthly_income * 0.25;
            $limit = $monthly_income - $recommended_savings;
        } elseif ($financial_goal === 'pagar_deudas' && $debt_amount > 0) {
            // Recommend paying 30% towards debt
            $recommended_payment = $monthly_income * 0.30;
            $limit = $monthly_income - $recommended_payment;
        } else {
            // For general control, recommend 80% spending limit
            $limit = $monthly_income * 0.80;
        }

        return round($limit, 2);
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

