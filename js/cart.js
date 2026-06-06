/**
 * Shopping cart AJAX handlers
 */
document.addEventListener('DOMContentLoaded', function () {
    var badge = document.getElementById('cart-badge');

    function updateBadge(count) {
        if (badge && typeof count !== 'undefined') {
            badge.textContent = count;
        }
    }

    function postCart(action, data) {
        var formData = new FormData();
        formData.append('action', action);
        Object.keys(data).forEach(function (key) {
            formData.append(key, data[key]);
        });
        return fetch('actions/cart-action.php', {
            method: 'POST',
            body: formData
        }).then(function (r) { return r.json(); });
    }

    document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId = btn.getAttribute('data-product-id');
            var qty = 1;
            var qtyInputId = btn.getAttribute('data-qty-input');
            if (qtyInputId) {
                var input = document.getElementById(qtyInputId);
                if (input) qty = parseInt(input.value, 10) || 1;
            }
            btn.disabled = true;
            postCart('add', { product_id: productId, quantity: qty })
                .then(function (res) {
                    btn.disabled = false;
                    if (res.success) {
                        updateBadge(res.cart_count);
                        btn.innerHTML = '<i class="bi bi-check-lg"></i> Added';
                        setTimeout(function () {
                            btn.innerHTML = '<i class="bi bi-bag-plus"></i> Add to Cart';
                        }, 1500);
                    } else {
                        alert(res.message || 'Could not add to cart.');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    alert('Network error. Please try again.');
                });
        });
    });

    document.querySelectorAll('.cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var row = input.closest('tr');
            var cartId = row.getAttribute('data-cart-id');
            var productId = row.getAttribute('data-product-id');
            var qty = parseInt(input.value, 10) || 1;
            postCart('update', { cart_id: cartId, product_id: productId, quantity: qty })
                .then(function (res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.message || 'Update failed.');
                    }
                });
        });
    });

    document.querySelectorAll('.remove-cart-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Remove this item from cart?')) return;
            var row = btn.closest('tr');
            var cartId = row.getAttribute('data-cart-id');
            var productId = row.getAttribute('data-product-id');
            postCart('remove', { cart_id: cartId, product_id: productId })
                .then(function (res) {
                    if (res.success) {
                        row.remove();
                        updateBadge(res.cart_count);
                        if (!document.querySelector('.cart-table tbody tr')) {
                            location.reload();
                        }
                    }
                });
        });
    });
});
