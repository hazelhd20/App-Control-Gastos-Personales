<?php
$page_title = 'Transacciones - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$transaction_model = new Transaction($db);
$profile_model = new FinancialProfile($db);

$user_id = $_SESSION['user_id'];
$profile = $profile_model->getByUserId($user_id);

// Get filter parameters
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$filter_category = $_GET['category'] ?? 'all';
$filter_type = $_GET['filter_type'] ?? 'all';

// Get transactions
$transactions = $transaction_model->getByMonth($user_id, $year, $month);

// Apply filters
if ($filter_category !== 'all') {
    $transactions = array_filter($transactions, function($t) use ($filter_category) {
        return $t['category'] === $filter_category;
    });
}

if ($filter_type !== 'all') {
    $transactions = array_filter($transactions, function($t) use ($filter_type) {
        return $t['type'] === $filter_type;
    });
}

$summary = $transaction_model->getMonthlySummary($user_id, $year, $month);
$categories = $transaction_model->getExpensesByCategory($user_id, $year, $month);

$flash = getFlashMessage();
?>

<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                    <i class="fas fa-list mr-2 sm:mr-3 text-blue-600"></i>Transacciones
                </h1>
                <p class="text-sm sm:text-base text-gray-600 mt-2">Historial de movimientos financieros</p>
            </div>
            <a href="<?php echo BASE_URL; ?>public/index.php?page=add-transaction" 
               class="btn-primary py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg font-semibold text-center text-sm sm:text-base w-full sm:w-auto">
                <i class="fas fa-plus mr-2"></i>Nueva Transacción
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <p class="text-xs sm:text-sm text-gray-600 mb-2">
                    <i class="fas fa-arrow-down text-green-600 mr-2"></i>Ingresos
                </p>
                <p class="text-xl sm:text-2xl font-bold text-green-600">
                    <?php echo formatCurrency($summary['total_income'] ?? 0, $profile['currency']); ?>
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <p class="text-xs sm:text-sm text-gray-600 mb-2">
                    <i class="fas fa-arrow-up text-red-600 mr-2"></i>Gastos
                </p>
                <p class="text-xl sm:text-2xl font-bold text-red-600">
                    <?php echo formatCurrency($summary['total_expenses'] ?? 0, $profile['currency']); ?>
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <p class="text-xs sm:text-sm text-gray-600 mb-2">
                    <i class="fas fa-receipt mr-2 text-blue-600"></i>Total de Transacciones
                </p>
                <p class="text-xl sm:text-2xl font-bold text-blue-600">
                    <?php echo count($transactions); ?>
                </p>
            </div>
        </div>

        <!-- Filters and Export -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <input type="hidden" name="page" value="transactions">
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select id="year" name="year" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                    <select id="month" name="month" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php
                        $months = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        foreach ($months as $num => $name):
                        ?>
                            <option value="<?php echo $num; ?>" <?php echo $num === $month ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select id="filter_type" name="filter_type" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>Todos</option>
                        <option value="income" <?php echo $filter_type === 'income' ? 'selected' : ''; ?>>Ingresos</option>
                        <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>Gastos</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select id="category" name="category" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todas</option>
                        <?php foreach ($categories as $cat): 
                            $cat_icon = $cat['category_icon'] ?? 'fa-tag';
                            $cat_color = $cat['category_color'] ?? '#6B7280';
                        ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    data-icon="<?php echo htmlspecialchars($cat_icon); ?>"
                                    data-color="<?php echo htmlspecialchars($cat_color); ?>"
                                    <?php echo $filter_category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:space-x-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 text-white py-2 px-3 sm:px-4 rounded-lg hover:bg-blue-700 transition text-sm sm:text-base">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                    <a href="<?php echo BASE_URL; ?>public/index.php?action=export-transactions&year=<?php echo $year; ?>&month=<?php echo $month; ?>" 
                       class="bg-green-600 text-white py-2 px-3 sm:px-4 rounded-lg hover:bg-green-700 transition text-sm sm:text-base flex items-center justify-center">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <?php if (!empty($transactions)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Categoría
                                </th>
                                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Método
                                </th>
                                <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto
                                </th>
                                <th class="px-3 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($transactions as $trans): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <?php 
                                        $category_icon = $trans['category_icon'] ?? 'fa-tag';
                                        $category_color = $trans['category_color'] ?? ($trans['type'] === 'expense' ? '#EF4444' : '#10B981');
                                        ?>
                                        <span class="px-2 sm:px-3 py-1 inline-flex items-center gap-1.5 sm:gap-2 text-xs leading-5 font-semibold rounded-full 
                                                     <?php echo $trans['type'] === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <i class="fas <?php echo htmlspecialchars($category_icon); ?>" style="color: <?php echo htmlspecialchars($category_color); ?>;"></i>
                                            <?php echo htmlspecialchars($trans['category']); ?>
                                        </span>
                                    </td>
                                    <td class="hidden md:table-cell px-6 py-3 sm:py-4 text-sm text-gray-600 max-w-xs truncate">
                                        <?php echo htmlspecialchars($trans['description'] ?: '-'); ?>
                                    </td>
                                    <td class="hidden lg:table-cell px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php 
                                        if ($trans['payment_method']) {
                                            $payment_icon = $trans['payment_method'] === 'efectivo' ? 'fa-money-bill-wave' : 'fa-credit-card';
                                            $payment_color = $trans['payment_method'] === 'efectivo' ? 'text-green-600' : 'text-blue-600';
                                            echo '<span class="inline-flex items-center gap-2"><i class="fas ' . $payment_icon . ' ' . $payment_color . '"></i>' . ucfirst($trans['payment_method']) . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right text-xs sm:text-sm font-bold 
                                               <?php echo $trans['type'] === 'expense' ? 'text-red-600' : 'text-green-600'; ?>">
                                        <?php echo $trans['type'] === 'expense' ? '-' : '+'; ?>
                                        <?php echo formatCurrency($trans['amount'], $profile['currency']); ?>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <form method="POST" action="<?php echo BASE_URL; ?>public/index.php?action=delete-transaction" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta transacción?');" class="inline">
                                            <input type="hidden" name="transaction_id" value="<?php echo $trans['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 p-1 sm:p-2">
                                                <i class="fas fa-trash text-sm sm:text-base"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-16 text-gray-500">
                    <i class="fas fa-inbox text-6xl mb-4 opacity-50"></i>
                    <p class="text-lg mb-4">No hay transacciones para mostrar</p>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=add-transaction" 
                       class="btn-primary py-2 px-6 rounded-lg inline-block">
                        <i class="fas fa-plus mr-2"></i>Agregar Primera Transacción
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

