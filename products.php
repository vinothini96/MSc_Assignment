<?php
/**
 * Product Listing - search, filter, category, pagination
 */
$pageTitle = 'Products';
require_once __DIR__ . '/includes/init.php';
$db = getDB();

$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$featured = isset($_GET['featured']);
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$where = ['p.is_active = 1'];
$params = [];

if ($search) {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categorySlug) {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($featured) {
    $where[] = 'p.is_featured = 1';
}
if ($minPrice !== '') {
    $where[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = (float)$minPrice;
}
if ($maxPrice !== '') {
    $where[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = (float)$maxPrice;
}

$orderBy = match($sort) {
    'price_low' => 'COALESCE(p.sale_price, p.price) ASC',
    'price_high' => 'COALESCE(p.sale_price, p.price) DESC',
    'rating' => 'p.avg_rating DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC',
};

$whereSql = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / ITEMS_PER_PAGE));

// Fetch products
$sql = "SELECT p.*, c.name AS category_name FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $whereSql ORDER BY $orderBy LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header"><h2>All Products</h2></div>
    <div class="row">
        <!-- Sidebar filters -->
        <div class="col-lg-3 mb-4">
            <div class="card p-3">
                <h6>Filters</h6>
                <form method="get" action="">
                    <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Price</label>
                        <input type="number" name="min_price" class="form-control form-control-sm" value="<?= e($minPrice) ?>" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Price</label>
                        <input type="number" name="max_price" class="form-control form-control-sm" value="<?= e($maxPrice) ?>" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                    <a href="<?= BASE_URL ?>/products.php" class="btn btn-link btn-sm w-100">Clear</a>
                </form>
            </div>
        </div>
        <div class="col-lg-9">
            <p class="text-muted"><?= $total ?> product(s) found</p>
            <div class="row">
                <?php if (count($products) === 0): ?>
                    <div class="col-12"><div class="alert alert-info">No products found. Try different filters.</div></div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/includes/product-card.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): 
                        $qs = http_build_query(array_merge($_GET, ['page' => $i]));
                    ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= e($qs) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
