<?php
$pageTitle = 'Dashboard';
require_once 'includes/admin-header.php';

$stats = [
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'revenue' => (float) $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != "cancelled"')->fetchColumn(),
];

$recentOrders = $pdo->query('
    SELECT o.*, u.full_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 5
')->fetchAll();

$monthlySales = $pdo->query('
    SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, SUM(total_amount) AS total
    FROM orders WHERE status != "cancelled"
    GROUP BY month ORDER BY month DESC LIMIT 6
')->fetchAll();
$monthlySales = array_reverse($monthlySales);

$statusCounts = $pdo->query('SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status')->fetchAll();
?>

<h1 class="h3 mb-4" style="color:var(--primary)">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card"><p class="text-muted small mb-1">Products</p><h3><?= $stats['products'] ?></h3></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card accent"><p class="text-muted small mb-1">Orders</p><h3><?= $stats['orders'] ?></h3></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card"><p class="text-muted small mb-1">Customers</p><h3><?= $stats['users'] ?></h3></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card accent"><p class="text-muted small mb-1">Revenue</p><h3><?= format_price($stats['revenue']) ?></h3></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="chart-container">
            <h6 class="mb-3">Monthly Sales</h6>
            <canvas id="salesChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-container">
            <h6 class="mb-3">Orders by Status</h6>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="chart-container mt-4">
    <h6 class="mb-3">Recent Orders</h6>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><a href="orders.php?id=<?= $o['id'] ?>"><?= e($o['order_number']) ?></a></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= format_price((float) $o['total_amount']) ?></td>
                    <td><span class="badge bg-secondary"><?= e($o['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.adminChartData = {
    months: <?= json_encode(array_column($monthlySales, 'month')) ?>,
    sales: <?= json_encode(array_map('floatval', array_column($monthlySales, 'total'))) ?>,
    statuses: <?= json_encode(array_column($statusCounts, 'status')) ?>,
    statusCounts: <?= json_encode(array_map('intval', array_column($statusCounts, 'cnt'))) ?>
};
</script>
<?php
$extraScripts = ['../js/admin-charts.js'];
require_once 'includes/admin-footer.php';
