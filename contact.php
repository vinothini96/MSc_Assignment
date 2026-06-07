<?php
/**
 * Contact Us Page
 *
 * - Logged-in users: name and email pre-filled from session/DB.
 *   Their user_id is linked to the message so they can see replies
 *   in their "My Messages" tab below the form.
 * - Guests: fill name and email manually.
 *   Their messages are stored but they cannot track replies online.
 */
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

// ── Load this user's previous messages + any admin replies ────────────────────
$myMessages = [];
if (is_logged_in()) {
    $stmt = $pdo->prepare(
        'SELECT * FROM contact_messages
         WHERE user_id = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $myMessages = $stmt->fetchAll();
}
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Contact</li>
        </ol>
    </nav>

    <div class="row g-5 mt-2">

        <!-- ── Left: contact info ──────────────────────────────────────── -->
        <div class="col-lg-5">
            <h1 class="section-title">Get in Touch</h1>
            <p class="text-muted">
                Have questions about a saree, bulk orders, or custom draping?
                We are happy to help.
            </p>
            <ul class="list-unstyled mt-4">
                <li class="mb-3">
                    <i class="bi bi-geo-alt text-primary me-2"></i>
                    123 Fashion Street, Polonnaruwa, Sri Lanka
                </li>
                <li class="mb-3">
                    <i class="bi bi-telephone text-primary me-2"></i>
                    +94 778 082 394
                </li>
                <li class="mb-3">
                    <i class="bi bi-envelope text-primary me-2"></i>
                    info@elegancesarees.com
                </li>
                <li>
                    <i class="bi bi-clock text-primary me-2"></i>
                    Mon–Sat: 10:00 AM – 8:00 PM
                </li>
            </ul>

            <?php if (!is_logged_in()): ?>
            <div class="alert alert-info mt-4 small">
                <i class="bi bi-info-circle me-1"></i>
                <a href="login.php?redirect=contact.php">Login</a> to track replies
                to your messages in your account.
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Right: contact form ────────────────────────────────────── -->
        <div class="col-lg-7">
            <div class="summary-box">
                <h5 class="mb-3">Send Us a Message</h5>
                <form action="actions/contact-process.php"
                      method="post"
                      id="contact-form"
                      novalidate>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="<?= e($_SESSION['user_name'] ?? '') ?>"
                                   required
                                   minlength="2">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= e($_SESSION['user_email'] ?? '') ?>"
                                   required
                                   data-validate="email"
                                   <?= is_logged_in() ? 'readonly' : '' ?>>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text"
                                   name="subject"
                                   class="form-control"
                                   placeholder="e.g. Question about silk sarees">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message"
                                      class="form-control"
                                      rows="5"
                                      required
                                      minlength="10"
                                      placeholder="Write your message here..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-elegance">
                                <i class="bi bi-send me-1"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── My Messages section (logged-in users only) ─────────────────── -->
    <?php if (is_logged_in()): ?>
    <div class="mt-5">
        <h4 class="section-title">My Messages</h4>

        <?php if (!$myMessages): ?>
        <p class="text-muted">You have not sent any messages yet.</p>

        <?php else: ?>
        <div class="accordion" id="messagesAccordion">
            <?php foreach ($myMessages as $i => $msg): ?>
            <div class="accordion-item mb-2 border-0 shadow-sm rounded overflow-hidden">

                <h2 class="accordion-header">
                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#msg-<?= (int) $msg['id'] ?>">

                        <!-- Status badge -->
                        <?php
                        $badgeClass = match($msg['status']) {
                            'replied' => 'bg-success',
                            'read'    => 'bg-info',
                            default   => 'bg-warning text-dark',
                        };
                        $badgeText = match($msg['status']) {
                            'replied' => 'Replied',
                            'read'    => 'Read',
                            default   => 'Pending',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?> me-2"><?= $badgeText ?></span>

                        <span class="fw-semibold me-3">
                            <?= $msg['subject'] ? e($msg['subject']) : 'No subject' ?>
                        </span>
                        <small class="text-muted ms-auto me-3">
                            <?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?>
                        </small>
                    </button>
                </h2>

                <div id="msg-<?= (int) $msg['id'] ?>"
                     class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                     data-bs-parent="#messagesAccordion">
                    <div class="accordion-body bg-white">

                        <!-- Original message -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <p class="small text-muted mb-1">
                                <i class="bi bi-person me-1"></i>
                                <strong>Your message</strong>
                                — <?= date('d M Y, h:i A', strtotime($msg['created_at'])) ?>
                            </p>
                            <p class="mb-0"><?= nl2br(e($msg['message'])) ?></p>
                        </div>

                        <!-- Admin reply (if any) -->
                        <?php if ($msg['status'] === 'replied' && $msg['admin_reply']): ?>
                        <div class="p-3 rounded"
                             style="background:var(--cream,#fdf6f0);border-left:4px solid var(--primary,#8b2252);">
                            <p class="small text-muted mb-1">
                                <i class="bi bi-shield-check me-1"></i>
                                <strong>Elegance Sarees Team</strong>
                                — <?= date('d M Y, h:i A', strtotime($msg['replied_at'])) ?>
                            </p>
                            <p class="mb-0"><?= nl2br(e($msg['admin_reply'])) ?></p>
                        </div>
                        <?php elseif ($msg['status'] === 'read'): ?>
                        <p class="text-info small mb-0">
                            <i class="bi bi-eye me-1"></i>
                            Your message has been read. A reply is on the way.
                        </p>
                        <?php else: ?>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-hourglass-split me-1"></i>
                            Awaiting reply from our team.
                        </p>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
?>
