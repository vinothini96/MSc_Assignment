<?php if (!isset($isLoginPage) || !$isLoginPage): ?>
    </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
