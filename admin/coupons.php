<?php
/**
 * Admin Coupon Management
 */
$adminPageTitle = 'Coupons';
require_once __DIR__ . '/../includes/init.php';
$db = getDB();

if (isset($_GET['delete'])) {
    $db->prepare('DELETE FROM coupons WHERE id = ?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'Coupon deleted.');
    redirect(BASE_URL . '/admin/coupons.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = $_POST['discount_type'] ?? 'percent';
    $value = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['min_order'] ?? 0);
    $maxUses = $_POST['max_uses'] !== '' ? (int)$_POST['max_uses'] : null;
    $expires = $_POST['expires_at'] ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        $db->prepare('UPDATE coupons SET code=?, discount_type=?, discount_value=?, min_order=?, max_uses=?, expires_at=?, is_active=? WHERE id=?')
            ->execute([$code, $type, $value, $minOrder, $maxUses, $expires, $isActive, $id]);
    } else {
        $db->prepare('INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses, expires_at, is_active) VALUES (?,?,?,?,?,?,?)')
            ->execute([$code, $type, $value, $minOrder, $maxUses, $expires, $isActive]);
    }
    setFlash('success', 'Coupon saved.');
    redirect(BASE_URL . '/admin/coupons.php');
}

$coupons = $db->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between mb-4">
    <h2>Discount Coupons</h2>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#couponModal">+ Add Coupon</button>
</div>

<table class="table table-striped bg-white">
    <thead class="table-dark"><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Expires</th><th>Active</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($coupons as $c): ?>
    <tr>
        <td><strong><?= e($c['code']) ?></strong></td>
        <td><?= e($c['discount_type']) ?></td>
        <td><?= $c['discount_type'] === 'percent' ? $c['discount_value'].'%' : formatPrice((float)$c['discount_value']) ?></td>
        <td><?= formatPrice((float)$c['min_order']) ?></td>
        <td><?= (int)$c['used_count'] ?><?= $c['max_uses'] ? '/'.$c['max_uses'] : '' ?></td>
        <td><?= e($c['expires_at'] ?? 'Never') ?></td>
        <td><?= $c['is_active'] ? 'Yes' : 'No' ?></td>
        <td><a href="?delete=<?= (int)$c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="couponModal">
    <div class="modal-dialog"><form method="post" class="modal-content">
        <div class="modal-header"><h5>Add Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label>Code</label><input type="text" name="code" class="form-control" required></div>
            <div class="mb-2"><label>Type</label><select name="discount_type" class="form-select"><option value="percent">Percent</option><option value="fixed">Fixed Amount</option></select></div>
            <div class="mb-2"><label>Value</label><input type="number" step="0.01" name="discount_value" class="form-control" required></div>
            <div class="mb-2"><label>Min Order</label><input type="number" step="0.01" name="min_order" class="form-control" value="0"></div>
            <div class="mb-2"><label>Max Uses (blank=unlimited)</label><input type="number" name="max_uses" class="form-control"></div>
            <div class="mb-2"><label>Expires</label><input type="date" name="expires_at" class="form-control"></div>
            <div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" checked> Active</div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-warning">Save</button></div>
    </form></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
