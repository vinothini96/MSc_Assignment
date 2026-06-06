<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-elegance mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Contact</li>
        </ol>
    </nav>

    <div class="row g-5 mt-2">
        <div class="col-lg-5">
            <h1 class="section-title">Get in Touch</h1>
            <p class="text-muted">Have questions about a saree, bulk orders, or custom draping? We are happy to help.</p>
            <ul class="list-unstyled">
                <li class="mb-3"><i class="bi bi-geo-alt text-primary"></i> 123 Fashion Street, Polonnaruwa, Sri Lanka</li>
                <li class="mb-3"><i class="bi bi-telephone text-primary"></i> +94 778 082 394</li>
                <li class="mb-3"><i class="bi bi-envelope text-primary"></i> info@elegancesarees.com</li>
                <li><i class="bi bi-clock text-primary"></i> Mon–Sat: 10:00 AM – 8:00 PM</li>
            </ul>
        </div>
        <div class="col-lg-7">
            <div class="summary-box">
                <form action="actions/contact-process.php" method="post" id="contact-form" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="name" class="form-control" required minlength="2">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required data-validate="email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="5" required minlength="10"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary-elegance">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = ['js/validation.js'];
require_once 'includes/footer.php';
