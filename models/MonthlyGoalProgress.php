<?php
/**
 * Monthly Goal Progress Model
 * Maneja el seguimiento mensual del progreso hacia los objetivos financieros
 */

class MonthlyGoalProgress {
    private $conn;
    private $table = 'monthly_goal_progress';

    public $id;
    public $user_id;
    public $year;
    public $month;
    public $goal_type;
    public $planned_amount;
    public $actual_amount;
    public $adjustments;
    public $status;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear o actualizar el progreso mensual
     */
    public function createOrUpdate() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id,
                      year = :year,
                      month = :month,
                      goal_type = :goal_type,
                      planned_amount = :planned_amount,
                      actual_amount = :actual_amount,
                      adjustments = :adjustments,
                      status = :status,
                      notes = :notes
                  ON DUPLICATE KEY UPDATE
                      planned_amount = :planned_amount,
                      actual_amount = :actual_amount,
                      adjustments = :adjustments,
                      status = :status,
                      notes = :notes,
                      updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':month', $this->month);
        $stmt->bindParam(':goal_type', $this->goal_type);
        $stmt->bindParam(':planned_amount', $this->planned_amount);
        $stmt->bindParam(':actual_amount', $this->actual_amount);
        $stmt->bindParam(':adjustments', $this->adjustments);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);

        if ($stmt->execute()) {
            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }
            return true;
        }
        return false;
    }

    /**
     * Obtener progreso mensual para un usuario, año y mes específicos
     */
    public function getByUserMonth($user_id, $year, $month, $goal_type = null) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND year = :year 
                  AND month = :month";
        
        if ($goal_type) {
            $query .= " AND goal_type = :goal_type";
        }
        
        $query .= " ORDER BY goal_type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        
        if ($goal_type) {
            $stmt->bindParam(':goal_type', $goal_type);
        }

        $stmt->execute();

        if ($goal_type) {
            return $stmt->fetch();
        } else {
            return $stmt->fetchAll();
        }
    }

    /**
     * Obtener progreso del mes actual
     */
    public function getCurrentMonth($user_id, $goal_type = null) {
        $year = date('Y');
        $month = date('m');
        return $this->getByUserMonth($user_id, $year, $month, $goal_type);
    }

    /**
     * Obtener historial de progreso mensual
     */
    public function getHistory($user_id, $goal_type = null, $limit = 12) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id";
        
        if ($goal_type) {
            $query .= " AND goal_type = :goal_type";
        }
        
        $query .= " ORDER BY year DESC, month DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($goal_type) {
            $stmt->bindParam(':goal_type', $goal_type);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Actualizar monto real del progreso
     */
    public function updateActualAmount($user_id, $year, $month, $goal_type, $actual_amount) {
        $query = "UPDATE " . $this->table . " 
                  SET actual_amount = :actual_amount,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE user_id = :user_id 
                  AND year = :year 
                  AND month = :month 
                  AND goal_type = :goal_type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':actual_amount', $actual_amount);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':goal_type', $goal_type);

        return $stmt->execute();
    }

    /**
     * Agregar ajuste al progreso mensual
     */
    public function addAdjustment($user_id, $year, $month, $goal_type, $adjustment_amount, $notes = null) {
        $query = "UPDATE " . $this->table . " 
                  SET adjustments = adjustments + :adjustment_amount,
                      status = 'adjusted',
                      notes = CONCAT(COALESCE(notes, ''), 
                                     IF(notes IS NOT NULL, '\n', ''), 
                                     :notes),
                      updated_at = CURRENT_TIMESTAMP
                  WHERE user_id = :user_id 
                  AND year = :year 
                  AND month = :month 
                  AND goal_type = :goal_type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':adjustment_amount', $adjustment_amount);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':goal_type', $goal_type);

        return $stmt->execute();
    }

    /**
     * Calcular el progreso total (actual + ajustes)
     * Nota: Este método puede retornar valores negativos para savings/debt si se gastó más de lo ganado
     */
    public function getTotalProgress($user_id, $year, $month, $goal_type) {
        $progress = $this->getByUserMonth($user_id, $year, $month, $goal_type);
        
        if ($progress) {
            return floatval($progress['actual_amount']) + floatval($progress['adjustments']);
        }
        
        return 0;
    }

    /**
     * Calcular el porcentaje de cumplimiento
     * Nota: Para spending_control, el porcentaje indica qué tanto se ha gastado del límite
     */
    public function getCompletionPercentage($user_id, $year, $month, $goal_type) {
        $progress = $this->getByUserMonth($user_id, $year, $month, $goal_type);
        
        if ($progress && $progress['planned_amount'] > 0) {
            $total_progress = floatval($progress['actual_amount']) + floatval($progress['adjustments']);
            
            if ($goal_type === 'spending_control') {
                // Para control de gastos, el porcentaje es gastos / límite
                return min(100, max(0, ($total_progress / $progress['planned_amount']) * 100));
            } else {
                // Para savings y debt_payment, solo contar progreso positivo
                $positive_progress = max(0, $total_progress);
                return min(100, max(0, ($positive_progress / $progress['planned_amount']) * 100));
            }
        }
        
        return 0;
    }

    /**
     * Verificar si existe progreso para un mes específico
     */
    public function exists($user_id, $year, $month, $goal_type) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND year = :year 
                  AND month = :month 
                  AND goal_type = :goal_type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':goal_type', $goal_type);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Eliminar progreso mensual
     */
    public function delete($user_id, $year, $month, $goal_type) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND year = :year 
                  AND month = :month 
                  AND goal_type = :goal_type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':goal_type', $goal_type);

        return $stmt->execute();
    }
}

