<?php
/**
 * Admin — Contact Messages
 *
 * Lists all customer contact messages.
 * Admin can:
 *   - View each message (auto-marks it as 'read')
 *   - Reply to a message (marks it as 'replied', stores reply + timestamp)
 *   - Delete a message
 *   - Filter by status: all / unread / read / replied
 *
 * Reply is stored in contact_messages.admin_reply and shown to the
 * customer on the contact.php "My Messages" section.
 */

$pageTitle = 'Messages';
require_once 'includes/admin-header.php';

$currentAdminId = (int) $_SESSION['admin_id'];

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')
        ->execute([(int) $_GET['delete']]);
    flash('success', 'Message deleted.');
    redirect('messages.php');
}

// ── REPLY (POST) ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply') {
    $msgId = (int) ($_POST['message_id'] ?? 0);
    $reply = trim($_POST['admin_reply'] ?? '');

    if (strlen($reply) < 2) {
        flash('danger', 'Reply cannot be empty.');
        redirect('messages.php?view=' . $msgId);
    }

    $pdo->prepare(
        'UPDATE contact_messages
         SET admin_reply = ?, replied_at = NOW(), replied_by = ?, status = ?
         WHERE id = ?'
    )->execute([$reply, $currentAdminId, 'replied', $msgId]);

    flash('success', 'Reply sent successfully.');
    redirect('messages.php?view=' . $msgId);
}

// ── MARK AS READ when viewing ─────────────────────────────────────────────────
$viewId = isset($_GET['view']) && is_numeric($_GET['view'])
    ? (int) $_GET['view']
    : 0;

if ($viewId) {
    // Only update if currently 'unread' — don't overwrite 'replied'
    $pdo->prepare(
        "UPDATE contact_messages SET status = 'read'
         WHERE id = ? AND status = 'unread'"
    )->execute([$viewId]);
}

// ── FILTER ────────────────────────────────────────────────────────────────────
$allowedFilters = ['all', 'unread', 'read', 'replied'];
$filter = in_array($_GET['filter'] ?? '', $allowedFilters)
    ? $_GET['filter']
    : 'all';

// ── LOAD MESSAGES ─────────────────────────────────────────────────────────────
if ($filter === 'all') {
    $messages = $pdo->query(
        'SELECT cm.*, u.full_name AS user_full_name
         FROM contact_messages cm
         LEFT JOIN users u ON u.id = cm.user_id
         ORDER BY
           FIELD(cm.status, "unread", "read", "replied"),
           cm.created_at DESC'
    )->fetchAll();
} else {
    $stmt = $pdo->prepare(
        'SELECT cm.*, u.full_name AS user_full_name
         FROM contact_messages cm
         LEFT JOIN users u ON u.id = cm.user_id
         WHERE cm.status = ?
         ORDER BY cm.created_at DESC'
    );
    $stmt->execute([$filter]);
    $messages = $stmt->fetchAll();
}

// ── LOAD SELECTED MESSAGE DETAIL ──────────────────────────────────────────────
$viewMessage = null;
if ($viewId) {
    $stmt = $pdo->prepare(
        'SELECT cm.*, u.full_name AS user_full_name, u.email AS user_account_email
         FROM contact_messages cm
         LEFT JOIN users u ON u.id = cm.user_id
         WHERE cm.id = ?'
    );
    $stmt->execute([$viewId]);
    $viewMessage = $stmt->fetch();
}

// ── UNREAD COUNT (for badge) ──────────────────────────────────────────────────
$unreadCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'"
)->fetchColumn();
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0" style="color:var(--primary)">
        <i class="bi bi-envelope me-2"></i>Contact Messages
        <?php if ($unreadCount > 0): ?>
        <span class="badge bg-danger ms-1"><?= $unreadCount ?> new</span>
        <?php endif; ?>
    </h1>
</div>

<!-- Filter tabs -->
<ul class="nav nav-tabs mb-4">
    <?php
    $tabs = [
        'all'     => 'All',
        'unread'  => 'Unread',
        'read'    => 'Read',
        'replied' => 'Replied',
    ];
    foreach ($tabs as $key => $label):
        $count = (int) $pdo->query(
            "SELECT COUNT(*) FROM contact_messages" .
            ($key !== 'all' ? " WHERE status = '$key'" : "")
        )->fetchColumn();
    ?>
    <li class="nav-item">
        <a class="nav-link <?= $filter === $key ? 'active' : '' ?>"
           href="messages.php?filter=<?= $key ?>">
            <?= $label ?>
            <span class="badge <?= $filter === $key ? 'bg-primary' : 'bg-secondary' ?> ms-1">
                <?= $count ?>
            </span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="row g-4">

    <!-- ── Message List ──────────────────────────────────────────────── -->
    <div class="col-lg-<?= $viewMessage ? '4' : '12' ?>">
        <div class="chart-container p-0">
            <?php if (!$messages): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4"></i>
                <p class="mt-2">No messages in this category.</p>
            </div>

            <?php else: ?>
            <div class="list-group list-group-flush rounded">
                <?php foreach ($messages as $msg):
                    $isActive  = $viewMessage && $viewMessage['id'] === $msg['id'];
                    $statusClass = match($msg['status']) {
                        'unread'  => 'border-start border-danger border-3',
                        'read'    => 'border-start border-info border-3',
                        'replied' => 'border-start border-success border-3',
                        default   => '',
                    };
                ?>
                <a href="messages.php?view=<?= (int) $msg['id'] ?>&filter=<?= e($filter) ?>"
                   class="list-group-item list-group-item-action <?= $isActive ? 'active' : '' ?> <?= $statusClass ?> py-3">

                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-2" style="min-width:0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <?php if ($msg['status'] === 'unread'): ?>
                                <span class="badge bg-danger">New</span>
                                <?php elseif ($msg['status'] === 'replied'): ?>
                                <span class="badge bg-success">Replied</span>
                                <?php else: ?>
                                <span class="badge bg-info">Read</span>
                                <?php endif; ?>
                                <span class="fw-semibold text-truncate">
                                    <?= e($msg['name']) ?>
                                </span>
                            </div>
                            <p class="mb-0 small text-truncate <?= $isActive ? 'text-white-50' : 'text-muted' ?>">
                                <?= $msg['subject'] ? e($msg['subject']) : e(substr($msg['message'], 0, 60)) . '...' ?>
                            </p>
                        </div>
                        <small class="text-nowrap <?= $isActive ? 'text-white-50' : 'text-muted' ?>">
                            <?= date('d M', strtotime($msg['created_at'])) ?>
                        </small>
                    </div>

                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Message Detail + Reply ────────────────────────────────────── -->
    <?php if ($viewMessage): ?>
    <div class="col-lg-8">
        <div class="chart-container">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">
                        <?= $viewMessage['subject']
                            ? e($viewMessage['subject'])
                            : '<span class="text-muted fst-italic">No subject</span>' ?>
                    </h5>
                    <p class="mb-0 text-muted small">
                        From: <strong><?= e($viewMessage['name']) ?></strong>
                        &lt;<?= e($viewMessage['email']) ?>&gt;
                        <?php if ($viewMessage['user_id']): ?>
                        — <span class="text-success small">
                            <i class="bi bi-person-check me-1"></i>Registered customer
                            (<?= e($viewMessage['user_full_name'] ?? 'Unknown') ?>)
                        </span>
                        <?php else: ?>
                        — <span class="text-muted small">
                            <i class="bi bi-person me-1"></i>Guest
                        </span>
                        <?php endif; ?>
                    </p>
                    <p class="mb-0 text-muted small">
                        Received: <?= date('d M Y, h:i A', strtotime($viewMessage['created_at'])) ?>
                    </p>
                </div>
                <a href="messages.php?delete=<?= (int) $viewMessage['id'] ?>&filter=<?= e($filter) ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this message permanently?')">
                    <i class="bi bi-trash"></i>
                </a>
            </div>

            <hr>

            <!-- Original message -->
            <div class="mb-4 p-3 bg-light rounded">
                <p class="small fw-semibold text-muted mb-2">
                    <i class="bi bi-chat-left-text me-1"></i> Customer Message
                </p>
                <p class="mb-0" style="white-space:pre-wrap;"><?= e($viewMessage['message']) ?></p>
            </div>

            <!-- Existing reply display -->
            <?php if ($viewMessage['status'] === 'replied' && $viewMessage['admin_reply']): ?>
            <div class="mb-4 p-3 rounded"
                 style="background:var(--cream,#fdf6f0);border-left:4px solid var(--primary,#8b2252);">
                <p class="small fw-semibold text-muted mb-2">
                    <i class="bi bi-reply me-1"></i>
                    Your reply —
                    <?= date('d M Y, h:i A', strtotime($viewMessage['replied_at'])) ?>
                </p>
                <p class="mb-0" style="white-space:pre-wrap;"><?= e($viewMessage['admin_reply']) ?></p>
                <p class="mt-2 mb-0 small text-muted">
                    This reply is visible to the customer on their Contact page.
                </p>
            </div>
            <?php endif; ?>

            <!-- Reply form -->
            <div>
                <h6 class="mb-3">
                    <i class="bi bi-reply-fill me-1"></i>
                    <?= $viewMessage['status'] === 'replied' ? 'Update Reply' : 'Write a Reply' ?>
                </h6>
                <form method="post" action="messages.php?view=<?= (int) $viewMessage['id'] ?>&filter=<?= e($filter) ?>">
                    <input type="hidden" name="action"     value="reply">
                    <input type="hidden" name="message_id" value="<?= (int) $viewMessage['id'] ?>">

                    <div class="mb-3">
                        <textarea name="admin_reply"
                                  class="form-control"
                                  rows="5"
                                  placeholder="Write your reply to <?= e($viewMessage['name']) ?>..."
                                  required><?= $viewMessage['admin_reply']
                                      ? e($viewMessage['admin_reply'])
                                      : '' ?></textarea>
                        <div class="form-text">
                            Your reply will be shown to the customer under
                            "My Messages" on the Contact page.
                            <?php if (!$viewMessage['user_id']): ?>
                            <span class="text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                This was a guest message — the customer must be
                                logged in to see online replies.
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-elegance">
                        <i class="bi bi-send me-1"></i>
                        <?= $viewMessage['status'] === 'replied' ? 'Update Reply' : 'Send Reply' ?>
                    </button>
                    <a href="messages.php?filter=<?= e($filter) ?>"
                       class="btn btn-secondary ms-2">
                        Close
                    </a>
                </form>
            </div>

        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/admin-footer.php'; ?>
