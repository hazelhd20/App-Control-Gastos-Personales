<?php
/**
 * Goal Progress Helper
 * Helper para inicializar y actualizar el progreso mensual de objetivos financieros
 */

require_once __DIR__ . '/../models/FinancialProfile.php';
require_once __DIR__ . '/../models/MonthlyGoalProgress.php';
require_once __DIR__ . '/../models/Transaction.php';

class GoalProgressHelper {
    private $db;
    private $profile_model;
    private $progress_model;
    private $transaction_model;

    public function __construct($db) {
        $this->db = $db;
        $this->profile_model = new FinancialProfile($db);
        $this->progress_model = new MonthlyGoalProgress($db);
        $this->transaction_model = new Transaction($db);
    }

    /**
     * Inicializar o actualizar el objetivo mensual para el mes actual
     * Se llama automáticamente al cargar el dashboard o cuando se necesita
     */
    public function initializeCurrentMonthGoal($user_id) {
        $year = date('Y');
        $month = date('m');
        
        return $this->initializeMonthGoal($user_id, $year, $month);
    }

    /**
     * Inicializar o actualizar el objetivo mensual para un mes específico
     */
    public function initializeMonthGoal($user_id, $year, $month) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return false; // No hay objetivo financiero configurado
        }

        // Calcular el monto planificado usando el cálculo inicial
        $planned_amount = $this->profile_model->calculateMonthlyGoalAmount($user_id);
        
        if ($planned_amount <= 0) {
            return false; // No hay monto planificado válido
        }

        // Verificar si ya existe un registro para este mes
        $exists = $this->progress_model->exists($user_id, $year, $month, $goal_type);
        
        if (!$exists) {
            // Crear nuevo registro con el objetivo planificado
            $this->progress_model->user_id = $user_id;
            $this->progress_model->year = $year;
            $this->progress_model->month = $month;
            $this->progress_model->goal_type = $goal_type;
            $this->progress_model->planned_amount = $planned_amount;
            $this->progress_model->actual_amount = 0;
            $this->progress_model->adjustments = 0;
            $this->progress_model->status = 'in_progress';
            $this->progress_model->notes = null;
            
            return $this->progress_model->createOrUpdate();
        } else {
            // Actualizar el monto planificado si cambió (por ejemplo, si se actualizó el perfil)
            $current_progress = $this->progress_model->getByUserMonth($user_id, $year, $month, $goal_type);
            
            if ($current_progress && $current_progress['planned_amount'] != $planned_amount) {
                // Solo actualizar si el estado es 'in_progress' o 'pending'
                if (in_array($current_progress['status'], ['in_progress', 'pending'])) {
                    $this->progress_model->user_id = $user_id;
                    $this->progress_model->year = $year;
                    $this->progress_model->month = $month;
                    $this->progress_model->goal_type = $goal_type;
                    $this->progress_model->planned_amount = $planned_amount;
                    $this->progress_model->actual_amount = $current_progress['actual_amount'];
                    $this->progress_model->adjustments = $current_progress['adjustments'];
                    $this->progress_model->status = $current_progress['status'];
                    $this->progress_model->notes = $current_progress['notes'];
                    
                    return $this->progress_model->createOrUpdate();
                }
            }
        }

        return true;
    }

    /**
     * Actualizar el progreso real del mes actual basado en las transacciones
     */
    public function updateCurrentMonthProgress($user_id) {
        $year = date('Y');
        $month = date('m');
        
        return $this->updateMonthProgress($user_id, $year, $month);
    }

    /**
     * Actualizar el progreso real de un mes específico basado en las transacciones
     */
    public function updateMonthProgress($user_id, $year, $month) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return false;
        }

        // Asegurar que el objetivo mensual esté inicializado
        $this->initializeMonthGoal($user_id, $year, $month);

        // Calcular el progreso real del mes
        $actual_amount = $this->transaction_model->getMonthlyProgress($user_id, $year, $month, $goal_type);

        // Para 'spending_control', el método retorna negativo (gastos), convertir a positivo para almacenar
        // Para 'savings' y 'debt_payment', el monto es ingresos - gastos (puede ser negativo si gastó más de lo que ganó)
        // Almacenamos siempre el valor absoluto para spending_control, pero mantenemos el signo para savings/debt
        if ($goal_type === 'spending_control') {
            $actual_amount = abs($actual_amount);
        }

        // Actualizar el monto real
        // Nota: Para savings y debt_payment, si el valor es negativo significa que gastó más de lo que ganó ese mes
        return $this->progress_model->updateActualAmount($user_id, $year, $month, $goal_type, $actual_amount);
    }

    /**
     * Obtener el progreso del mes actual
     */
    public function getCurrentMonthProgress($user_id) {
        $year = date('Y');
        $month = date('m');
        
        return $this->getMonthProgress($user_id, $year, $month);
    }

    /**
     * Obtener el progreso de un mes específico
     */
    public function getMonthProgress($user_id, $year, $month) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return null;
        }

        // Asegurar que el objetivo mensual esté inicializado
        $this->initializeMonthGoal($user_id, $year, $month);
        
        // Actualizar el progreso real
        $this->updateMonthProgress($user_id, $year, $month);

        // Obtener el progreso completo
        $progress = $this->progress_model->getByUserMonth($user_id, $year, $month, $goal_type);
        
        if ($progress) {
            // Calcular el progreso total (actual + ajustes)
            $actual_amount = floatval($progress['actual_amount']);
            $adjustments = floatval($progress['adjustments']);
            $total_progress = $actual_amount + $adjustments;
            
            // Para spending_control, el progreso es inverso (menos gastos = mejor)
            if ($goal_type === 'spending_control') {
                $progress['total_progress'] = $total_progress; // Gastos totales
                $progress['completion_percentage'] = $progress['planned_amount'] > 0 
                    ? min(100, max(0, ($total_progress / $progress['planned_amount']) * 100)) 
                    : 0;
                $progress['remaining'] = max(0, $progress['planned_amount'] - $total_progress); // Disponible
                $progress['surplus'] = max(0, $total_progress - $progress['planned_amount']); // Excedido
            } else {
                // Para savings y debt_payment
                $progress['total_progress'] = max(0, $total_progress); // Solo mostrar positivo
                $progress['completion_percentage'] = $progress['planned_amount'] > 0 
                    ? min(100, max(0, ($progress['total_progress'] / $progress['planned_amount']) * 100)) 
                    : 0;
                $progress['remaining'] = max(0, $progress['planned_amount'] - $progress['total_progress']);
                $progress['surplus'] = max(0, $progress['total_progress'] - $progress['planned_amount']);
            }
        }

        return $progress;
    }

    /**
     * Agregar un ajuste al progreso mensual (retiros, gastos excepcionales, etc.)
     */
    public function addAdjustment($user_id, $year, $month, $adjustment_amount, $notes = null) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return false;
        }

        return $this->progress_model->addAdjustment($user_id, $year, $month, $goal_type, $adjustment_amount, $notes);
    }

    /**
     * Obtener el historial de progreso mensual
     */
    public function getProgressHistory($user_id, $limit = 12) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return [];
        }

        return $this->progress_model->getHistory($user_id, $goal_type, $limit);
    }
}

