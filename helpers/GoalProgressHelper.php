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
     * Actualizar el progreso real del mes actual
     * El progreso se basa en el monto planificado que se separa automáticamente cada mes
     */
    public function updateCurrentMonthProgress($user_id) {
        $year = date('Y');
        $month = date('m');
        
        return $this->updateMonthProgress($user_id, $year, $month);
    }

    /**
     * Actualizar el progreso de un mes específico
     * El progreso se basa en el monto planificado que se separa automáticamente cada mes
     * Para spending_control, se mantiene el cálculo basado en gastos reales
     */
    public function updateMonthProgress($user_id, $year, $month) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return false;
        }

        // Asegurar que el objetivo mensual esté inicializado
        $this->initializeMonthGoal($user_id, $year, $month);

        // Obtener el progreso del mes
        $progress = $this->progress_model->getByUserMonth($user_id, $year, $month, $goal_type);
        
        if (!$progress) {
            return false;
        }

        $planned_amount = floatval($progress['planned_amount']);

        // Para 'spending_control', calcular basado en gastos reales
        if ($goal_type === 'spending_control') {
            $actual_amount = $this->transaction_model->getMonthlyProgress($user_id, $year, $month, $goal_type);
            $actual_amount = abs($actual_amount);
        } else {
            // Para 'savings' y 'debt_payment', el progreso es el monto planificado
            // ya que cada mes se separa automáticamente esa cantidad según el objetivo financiero
            $actual_amount = $planned_amount;
        }

        // Actualizar el monto real
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
                // Para savings y debt_payment, el progreso es el monto planificado (se separa automáticamente)
                // El progreso siempre será 100% ya que se separa automáticamente cada mes
                $progress['total_progress'] = $progress['planned_amount']; // Siempre igual al planificado
                $progress['completion_percentage'] = 100; // Siempre 100% porque se separa automáticamente
                $progress['remaining'] = 0; // No falta nada, ya se separó
                $progress['surplus'] = max(0, $total_progress - $progress['planned_amount']); // Solo si hay ajustes positivos
            }
        }

        return $progress;
    }

    /**
     * Calcular el progreso acumulado total basado en todos los meses planificados
     * desde la fecha de inicio hasta la fecha actual
     */
    public function getAccumulatedProgress($user_id) {
        $goal_type = $this->profile_model->getGoalTypeForTracking($user_id);
        
        if (!$goal_type) {
            return 0;
        }

        $profile = $this->profile_model->getByUserId($user_id);
        
        if (!$profile || !$profile['start_date']) {
            return 0;
        }

        // Calcular meses desde la fecha de inicio hasta hoy
        $start_date = new DateTime($profile['start_date']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        // Si la fecha de inicio es futura, retornar 0
        if ($start_date > $today) {
            return 0;
        }

        // Obtener el monto planificado mensual
        $monthly_planned = $this->profile_model->calculateMonthlyGoalAmount($user_id);
        
        if ($monthly_planned <= 0) {
            return 0;
        }

        // Calcular el número de meses completos desde el inicio hasta hoy
        $current_year = (int)date('Y');
        $current_month = (int)date('m');
        $start_year = (int)$start_date->format('Y');
        $start_month = (int)$start_date->format('m');

        // Calcular meses transcurridos
        $months_elapsed = ($current_year - $start_year) * 12 + ($current_month - $start_month);
        
        // Si estamos en el mismo mes, contar como 1 mes
        if ($months_elapsed == 0) {
            $months_elapsed = 1;
        } else {
            // Sumar 1 para incluir el mes actual
            $months_elapsed += 1;
        }

        // El progreso acumulado es el monto planificado multiplicado por los meses transcurridos
        $accumulated = $monthly_planned * $months_elapsed;

        // Para spending_control, retornar 0 (no aplica progreso acumulado)
        if ($goal_type === 'spending_control') {
            return 0;
        }

        return round($accumulated, 2);
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

