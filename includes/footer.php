</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5><i class="bi bi-gem"></i> <?= e(APP_NAME) ?></h5>
                <p class="text-muted-small">Discover timeless elegance with our curated collection of silk, cotton, designer, and bridal sarees.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <h6>Shop</h6>
                <ul class="footer-links">
                    <li><a href="shop.php">All Sarees</a></li>
                    <li><a href="shop.php?category=silk-sarees">Silk</a></li>
                    <li><a href="shop.php?category=bridal-sarees">Bridal</a></li>
                    <li><a href="shop.php?filter=featured">Featured</a></li>
                </ul>
            </div>
            <div class="col-md-2 col-6">
                <h6>Account</h6>
                <ul class="footer-links">
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="cart.php">Cart</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Contact</h6>
                <p class="text-muted-small mb-1"><i class="bi bi-geo-alt"></i> 123 Fashion Street, Polonnaruwa, Sri Lanka</p>
                <p class="text-muted-small mb-1"><i class="bi bi-telephone"></i> +94 778 082 394</p>
                <p class="text-muted-small"><i class="bi bi-envelope"></i> info@elegancesarees.com</p>
            </div>
        </div>
        <hr class="footer-divider">
        <p class="text-center copyright mb-0">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
