<?php
/**
 * Transaction Model
 */

class Transaction {
    private $conn;
    private $table = 'transactions';

    public $id;
    public $user_id;
    public $type;
    public $amount;
    public $category;
    public $payment_method;
    public $description;
    public $transaction_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new transaction
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id,
                      type = :type,
                      amount = :amount,
                      category = :category,
                      payment_method = :payment_method,
                      description = :description,
                      transaction_date = :transaction_date";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':transaction_date', $this->transaction_date);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Get all transactions for a user
     */
    public function getByUserId($user_id, $limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY transaction_date DESC, created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get transactions by month
     */
    public function getByMonth($user_id, $year, $month) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND YEAR(transaction_date) = :year 
                  AND MONTH(transaction_date) = :month
                  ORDER BY transaction_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get transactions by date range
     */
    public function getByDateRange($user_id, $start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND transaction_date BETWEEN :start_date AND :end_date
                  ORDER BY transaction_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get monthly summary
     */
    public function getMonthlySummary($user_id, $year, $month) {
        $query = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
                    SUM(CASE WHEN type = 'expense' AND payment_method = 'efectivo' THEN amount ELSE 0 END) as cash_expenses,
                    SUM(CASE WHEN type = 'expense' AND payment_method = 'tarjeta' THEN amount ELSE 0 END) as card_expenses
                  FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND YEAR(transaction_date) = :year 
                  AND MONTH(transaction_date) = :month";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get expenses by category
     */
    public function getExpensesByCategory($user_id, $year, $month) {
        $query = "SELECT 
                    category,
                    SUM(amount) as total
                  FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND type = 'expense'
                  AND YEAR(transaction_date) = :year 
                  AND MONTH(transaction_date) = :month
                  GROUP BY category
                  ORDER BY total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get recent transactions
     */
    public function getRecent($user_id, $limit = 10) {
        return $this->getByUserId($user_id, $limit);
    }

    /**
     * Delete transaction
     */
    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Update transaction
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET amount = :amount,
                      category = :category,
                      payment_method = :payment_method,
                      description = :description,
                      transaction_date = :transaction_date
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':transaction_date', $this->transaction_date);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    /**
     * Get last transaction date
     */
    public function getLastTransactionDate($user_id) {
        $query = "SELECT MAX(transaction_date) as last_date 
                  FROM " . $this->table . " 
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['last_date'];
    }

    /**
     * Get expense categories
     */
    public function getCategories() {
        $query = "SELECT name, icon, color FROM expense_categories ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

