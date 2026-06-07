<?php
/**
 * Admin — Banner Management
 *
 * Handles full CRUD for homepage hero carousel banners.
 *
 * Each banner has:
 *   - title, subtitle, discount_text  → displayed in the carousel caption
 *   - image                           → filename in assets/images/banners/
 *                                       OR uploaded to uploads/banners/
 *   - link_url                        → "Shop Now" button destination
 *   - sort_order                      → carousel display sequence
 *   - is_active                       → show/hide without deleting
 *
 * Bootstrap path: uses admin-header.php (config + db + functions)
 * so $pdo, flash(), e(), redirect() are all available.
 */

$pageTitle = 'Banners';
require_once 'includes/admin-header.php';

// ── Ensure upload directory exists ────────────────────────────────────────────
$uploadDir     = dirname(__DIR__) . '/uploads/banners/';
$uploadDirUrl  = '../uploads/banners/';      // relative URL from /admin/
$assetBannerUrl = '../assets/images/banners/'; // relative URL from /admin/

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed image extensions for upload
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

/**
 * Handle file upload. Returns the saved filename on success, or existing
 * filename if no new file was provided, or null on error (adds to $errors).
 */
function handle_banner_upload(array &$errors, ?string $existingImage): ?string
{
    global $allowedExt, $uploadDir;

    if (empty($_FILES['image']['name'])) {
        return $existingImage; // no new file — keep what was there
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed (error code ' . $_FILES['image']['error'] . ').';
        return $existingImage;
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $errors[] = 'Invalid image type. Allowed: ' . implode(', ', $allowedExt);
        return $existingImage;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Image must be smaller than 5 MB.';
        return $existingImage;
    }

    $filename = 'banner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
        $errors[] = 'Could not save the uploaded image.';
        return $existingImage;
    }

    return $filename;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    // Fetch the image filename before deleting so we can remove the file
    $stmt = $pdo->prepare('SELECT image FROM banners WHERE id = ?');
    $stmt->execute([$deleteId]);
    $row = $stmt->fetch();

    $pdo->prepare('DELETE FROM banners WHERE id = ?')->execute([$deleteId]);

    // Remove the uploaded file if it lives in uploads/banners/
    if ($row && $row['image'] && file_exists($uploadDir . $row['image'])) {
        @unlink($uploadDir . $row['image']);
    }

    flash('success', 'Banner deleted.');
    redirect('banners.php');
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggleId = (int) $_GET['toggle'];
    $stmt = $pdo->prepare('SELECT is_active FROM banners WHERE id = ?');
    $stmt->execute([$toggleId]);
    $current = $stmt->fetch();
    if ($current) {
        $newState = $current['is_active'] ? 0 : 1;
        $pdo->prepare('UPDATE banners SET is_active = ? WHERE id = ?')->execute([$newState, $toggleId]);
        flash('success', 'Banner ' . ($newState ? 'activated' : 'deactivated') . '.');
    }
    redirect('banners.php');
}

// ── CREATE (POST with action=create) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title        = trim($_POST['title'] ?? '');
    $subtitle     = trim($_POST['subtitle'] ?? '');
    $discountText = trim($_POST['discount_text'] ?? '');
    $linkUrl      = trim($_POST['link_url'] ?? 'shop.php');
    $sortOrder    = (int) ($_POST['sort_order'] ?? 0);
    $isActive     = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];

    if (strlen($title) < 2) {
        $errors[] = 'Banner title is required (min 2 characters).';
    }
    if (strlen($linkUrl) < 1) {
        $errors[] = 'Link URL is required.';
    }

    $imageName = handle_banner_upload($errors, 'hero_banner_1.jpg');

    if (empty($errors)) {
        $pdo->prepare(
            'INSERT INTO banners (title, subtitle, discount_text, image, link_url, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([$title, $subtitle, $discountText, $imageName, $linkUrl, $sortOrder, $isActive]);

        flash('success', 'Banner "' . $title . '" created successfully.');
        redirect('banners.php');
    }

    // Keep form data for re-population on validation error
    $_SESSION['form_data'] = compact('title', 'subtitle', 'discountText', 'linkUrl', 'sortOrder', 'isActive');
    flash('danger', implode(' ', $errors));
    redirect('banners.php?action=create');
}

// ── EDIT (POST with action=edit) ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $editId       = (int) ($_POST['banner_id'] ?? 0);
    $title        = trim($_POST['title'] ?? '');
    $subtitle     = trim($_POST['subtitle'] ?? '');
    $discountText = trim($_POST['discount_text'] ?? '');
    $linkUrl      = trim($_POST['link_url'] ?? 'shop.php');
    $sortOrder    = (int) ($_POST['sort_order'] ?? 0);
    $isActive     = isset($_POST['is_active']) ? 1 : 0;
    $existingImg  = trim($_POST['existing_image'] ?? '');

    $errors = [];

    if (strlen($title) < 2) {
        $errors[] = 'Banner title is required (min 2 characters).';
    }

    $imageName = handle_banner_upload($errors, $existingImg);

    if (empty($errors)) {
        $pdo->prepare(
            'UPDATE banners
             SET title=?, subtitle=?, discount_text=?, image=?, link_url=?, sort_order=?, is_active=?
             WHERE id=?'
        )->execute([$title, $subtitle, $discountText, $imageName, $linkUrl, $sortOrder, $isActive, $editId]);

        flash('success', 'Banner updated.');
        redirect('banners.php');
    }

    flash('danger', implode(' ', $errors));
    redirect('banners.php?edit=' . $editId);
}

// ── LOAD DATA ─────────────────────────────────────────────────────────────────
$banners = $pdo->query('SELECT * FROM banners ORDER BY sort_order ASC, id ASC')->fetchAll();

// For edit mode — load the specific banner being edited
$editBanner = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM banners WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editBanner = $stmt->fetch();
}

$showCreateForm = isset($_GET['action']) && $_GET['action'] === 'create';

// Repopulate create form after validation failure
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Helper: resolve full banner image URL for preview (relative to /admin/)
function banner_preview_url(string $image): string
{
    global $uploadDir, $uploadDirUrl, $assetBannerUrl;

    // Check uploads/banners/ first (admin-uploaded)
    if ($image && file_exists($uploadDir . $image)) {
        return $uploadDirUrl . $image;
    }
    // Fall back to assets/images/banners/ (bundled)
    return $assetBannerUrl . $image;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0" style="color:var(--primary)">
        <i class="bi bi-images me-2"></i>Banner Management
    </h1>
    <?php if (!$showCreateForm && !$editBanner): ?>
    <a href="banners.php?action=create" class="btn btn-primary-elegance">
        <i class="bi bi-plus-lg me-1"></i> Add Banner
    </a>
    <?php endif; ?>
</div>

<?php if ($showCreateForm): ?>
<!-- ══════════════════════════════════════════════════════ CREATE FORM ══ -->
<div class="chart-container mb-4">
    <h5 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Add New Banner</h5>
    <form method="post" action="banners.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action" value="create">
        <div class="row g-3">

            <div class="col-md-8">
                <label class="form-label fw-semibold">Banner Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= e($formData['title'] ?? '') ?>"
                       placeholder="e.g. Festive Collection 2026"
                       required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?= (int)($formData['sortOrder'] ?? 0) ?>"
                       min="0" placeholder="0 = first">
                <div class="form-text">Lower number appears first in the carousel.</div>
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Subtitle</label>
                <input type="text" name="subtitle" class="form-control"
                       value="<?= e($formData['subtitle'] ?? '') ?>"
                       placeholder="e.g. Up to 30% off on silk sarees">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Discount Badge Text</label>
                <input type="text" name="discount_text" class="form-control"
                       value="<?= e($formData['discountText'] ?? '') ?>"
                       placeholder="e.g. 30% OFF">
                <div class="form-text">Shown as a small badge over the image.</div>
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Link URL <span class="text-danger">*</span></label>
                <input type="text" name="link_url" class="form-control"
                       value="<?= e($formData['linkUrl'] ?? 'shop.php') ?>"
                       placeholder="shop.php or shop.php?category=silk-sarees"
                       required>
                <div class="form-text">"Shop Now" button destination. Use relative paths like <code>shop.php?category=silk-sarees</code></div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Banner Image</label>
                <input type="file" name="image" class="form-control"
                       accept="image/jpeg,image/png,image/webp"
                       id="create-image-input">
                <div class="form-text">JPG, PNG, WEBP · Max 5 MB. Leave blank to use default.</div>
                <!-- Live preview -->
                <img id="create-image-preview"
                     src="<?= e(banner_preview_url('hero_banner_1.jpg')) ?>"
                     alt="Preview"
                     class="mt-2 rounded border"
                     style="width:100%;max-height:100px;object-fit:cover;display:block;">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="create-is-active"
                           <?= ($formData['isActive'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="create-is-active">
                        Active — show this banner on the homepage carousel
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 mt-1">
                <button type="submit" class="btn btn-primary-elegance">
                    <i class="bi bi-check-lg me-1"></i> Save Banner
                </button>
                <a href="banners.php" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </form>
</div>

<?php elseif ($editBanner): ?>
<!-- ══════════════════════════════════════════════════════ EDIT FORM ══ -->
<div class="chart-container mb-4">
    <h5 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Banner</h5>
    <form method="post" action="banners.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action"      value="edit">
        <input type="hidden" name="banner_id"   value="<?= (int) $editBanner['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= e($editBanner['image']) ?>">

        <div class="row g-3">

            <div class="col-md-8">
                <label class="form-label fw-semibold">Banner Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= e($editBanner['title']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?= (int) $editBanner['sort_order'] ?>" min="0">
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Subtitle</label>
                <input type="text" name="subtitle" class="form-control"
                       value="<?= e($editBanner['subtitle']) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Discount Badge Text</label>
                <input type="text" name="discount_text" class="form-control"
                       value="<?= e($editBanner['discount_text'] ?? '') ?>"
                       placeholder="e.g. 30% OFF">
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Link URL <span class="text-danger">*</span></label>
                <input type="text" name="link_url" class="form-control"
                       value="<?= e($editBanner['link_url']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Replace Image</label>
                <input type="file" name="image" class="form-control"
                       accept="image/jpeg,image/png,image/webp"
                       id="edit-image-input">
                <div class="form-text">Leave blank to keep the current image.</div>
                <!-- Current image preview -->
                <img id="edit-image-preview"
                     src="<?= e(banner_preview_url($editBanner['image'])) ?>"
                     alt="Current banner image"
                     class="mt-2 rounded border"
                     style="width:100%;max-height:100px;object-fit:cover;display:block;">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="edit-is-active"
                           <?= $editBanner['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="edit-is-active">
                        Active — show this banner on the homepage carousel
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 mt-1">
                <button type="submit" class="btn btn-primary-elegance">
                    <i class="bi bi-check-lg me-1"></i> Update Banner
                </button>
                <a href="banners.php" class="btn btn-secondary">Cancel</a>
            </div>

        </div>
    </form>
</div>

<?php endif; ?>

<!-- ══════════════════════════════════════════════════════ BANNERS LIST ══ -->
<?php if ($banners): ?>
<div class="row g-3">
    <?php foreach ($banners as $b): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 shadow-sm">

            <!-- Banner image preview -->
            <div style="height:140px;overflow:hidden;background:#f8f9fa;">
                <img src="<?= e(banner_preview_url($b['image'])) ?>"
                     alt="<?= e($b['title']) ?>"
                     style="width:100%;height:140px;object-fit:cover;">
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="card-title mb-0 fw-bold"><?= e($b['title']) ?></h6>
                    <span class="badge <?= $b['is_active'] ? 'bg-success' : 'bg-secondary' ?> ms-2">
                        <?= $b['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>

                <?php if ($b['subtitle']): ?>
                <p class="text-muted small mb-1"><?= e($b['subtitle']) ?></p>
                <?php endif; ?>

                <?php if ($b['discount_text']): ?>
                <span class="badge bg-warning text-dark mb-2"><?= e($b['discount_text']) ?></span>
                <?php endif; ?>

                <p class="small mb-0">
                    <i class="bi bi-link-45deg"></i>
                    <span class="text-muted"><?= e($b['link_url']) ?></span>
                </p>
                <p class="small mb-0">
                    <i class="bi bi-sort-numeric-down"></i>
                    <span class="text-muted">Order: <?= (int) $b['sort_order'] ?></span>
                </p>
            </div>

            <div class="card-footer bg-white border-top d-flex gap-2">
                <a href="banners.php?edit=<?= (int) $b['id'] ?>"
                   class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="banners.php?toggle=<?= (int) $b['id'] ?>"
                   class="btn btn-sm <?= $b['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                   title="<?= $b['is_active'] ? 'Deactivate' : 'Activate' ?>">
                    <i class="bi bi-<?= $b['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                </a>
                <a href="banners.php?delete=<?= (int) $b['id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete banner &quot;<?= e(addslashes($b['title'])) ?>&quot;? This cannot be undone.')"
                   title="Delete">
                    <i class="bi bi-trash"></i>
                </a>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="chart-container text-center py-5">
    <i class="bi bi-images display-1 text-muted"></i>
    <p class="mt-3 text-muted">No banners yet. Click <strong>Add Banner</strong> to create the first one.</p>
    <a href="banners.php?action=create" class="btn btn-primary-elegance mt-2">
        <i class="bi bi-plus-lg me-1"></i> Add Banner
    </a>
</div>
<?php endif; ?>

<script>
/**
 * Show a live image preview when a file is selected in a file input.
 */
function bindImagePreview(inputId, previewId) {
    var input   = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;

        // Validate type client-side for instant feedback
        var allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Invalid file type. Please choose a JPG, PNG, or WEBP image.');
            this.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Image is too large. Maximum size is 5 MB.');
            this.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    });
}

bindImagePreview('create-image-input', 'create-image-preview');
bindImagePreview('edit-image-input',   'edit-image-preview');
</script>

<?php require_once 'includes/admin-footer.php'; ?>
