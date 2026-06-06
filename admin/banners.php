<?php
/**
 * Admin Marketing Banners Management
 */
$adminPageTitle = 'Banners';
require_once __DIR__ . '/../includes/init.php';
$db = getDB();

if (isset($_GET['delete'])) {
    $db->prepare('DELETE FROM banners WHERE id = ?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'Banner deleted.');
    redirect(BASE_URL . '/admin/banners.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $link = trim($_POST['link_url'] ?? '#');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $image = $_POST['existing_image'] ?? 'banner1.svg';
    
    if ($id > 0) {
        $db->prepare('UPDATE banners SET title=?, subtitle=?, link_url=?, sort_order=?, is_active=?, image=? WHERE id=?')
            ->execute([$title, $subtitle, $link, $sort, $isActive, $image, $id]);
    } else {
        $db->prepare('INSERT INTO banners (title, subtitle, link_url, sort_order, is_active, image) VALUES (?,?,?,?,?,?)')
            ->execute([$title, $subtitle, $link, $sort, $isActive, $image]);
    }
    setFlash('success', 'Banner saved.');
    redirect(BASE_URL . '/admin/banners.php');
}

$banners = $db->query('SELECT * FROM banners ORDER BY sort_order')->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between mb-4">
    <h2>Marketing Banners</h2>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#bannerModal">+ Add Banner</button>
</div>

<div class="row g-3">
<?php foreach ($banners as $b): ?>
<div class="col-md-4">
    <div class="card p-3">
        <h5><?= e($b['title']) ?></h5>
        <p class="text-muted small"><?= e($b['subtitle']) ?></p>
        <p>Link: <?= e($b['link_url']) ?> | Order: <?= (int)$b['sort_order'] ?></p>
        <a href="?delete=<?= (int)$b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
    </div>
</div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="bannerModal">
    <div class="modal-dialog"><form method="post" class="modal-content">
        <div class="modal-header"><h5>Add Banner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label>Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-2"><label>Subtitle</label><input type="text" name="subtitle" class="form-control"></div>
            <div class="mb-2"><label>Link URL</label><input type="text" name="link_url" class="form-control" value="products.php"></div>
            <div class="mb-2"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
            <input type="hidden" name="existing_image" value="banner1.svg">
            <div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" checked> Active</div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-warning">Save</button></div>
    </form></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
