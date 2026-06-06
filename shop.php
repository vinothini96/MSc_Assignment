<?php
$pageTitle = 'Shop';
require_once 'includes/header.php';

$categorySlug = $_GET['category'] ?? '';
$search = trim($_GET['q'] ?? '');
$sareeType = $_GET['type'] ?? '';
$filter = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.status = "active"';
$params = [];

if ($categorySlug) {
    $sql .= ' AND c.slug = ?';
    $params[] = $categorySlug;
}
if ($search) {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR p.saree_type LIKE ?)';
    $term = '%' . $search . '%';
    $params = array_merge($params, [$term, $term, $term]);
}
if ($sareeType) {
    $sql .= ' AND p.saree_type = ?';
    $params[] = $sareeType;
}
if ($filter === 'featured') {
    $sql .= ' AND p.is_featured = 1';
}
if ($filter === 'new') {
    $sql .= ' AND p.is_new_arrival = 1';
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= ' AND COALESCE(p.discount_price, p.price) >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= ' AND COALESCE(p.discount_price, p.price) <= ?';
    $params[] = $maxPrice;
}

switch ($sort) {
    case 'price_low': $sql .= ' ORDER BY COALESCE(p.discount_price, p.price) ASC'; break;
    case 'price_high': $sql .= ' ORDER BY COALESCE(p.discount_price, p.price) DESC'; break;
    case 'name': $sql .= ' ORDER BY p.name ASC'; break;
    default: $sql .= ' ORDER BY p.created_at DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT * FROM categories WHERE status = "active"')->fetchAll();
$types = $pdo->query('SELECT DISTINCT saree_type FROM products WHERE status = "active" ORDER BY saree_type')->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Shop</li>
        </ol>
    </nav>

    <div class="row mt-3">
        <aside class="col-lg-3">
            <div class="filter-sidebar">
                <h6><i class="bi bi-funnel"></i> Filters</h6>
                <form method="get" action="shop.php" id="shop-filter-form">
                    <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Saree Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach ($types as $type): ?>
                            <option value="<?= e($type) ?>" <?= $sareeType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min Rs." value="<?= e($minPrice) ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max Rs." value="<?= e($maxPrice) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-elegance btn-sm w-100">Apply Filters</button>
                    <a href="shop.php" class="btn btn-link btn-sm w-100 mt-1">Clear All</a>
                </form>
            </div>
        </aside>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0" style="color: var(--primary);">
                    <?= $search ? 'Results for "' . e($search) . '"' : 'Saree Collection' ?>
                </h1>
                <span class="text-muted"><?= count($products) ?> products</span>
            </div>
            <?php if (!$products): ?>
            <div class="alert alert-info">No sarees found. Try adjusting your filters.</div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): echo render_product_card($product); endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$extraScripts = ['js/cart.js'];
require_once 'includes/footer.php';
