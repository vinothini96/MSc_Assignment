/**
 * AJAX cart operations - add, update, remove without full page reload
 */

function updateCartBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (badge) badge.textContent = count;
}

function addToCart(productId, quantity = 1) {
    fetch(BASE_URL + '/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, quantity: quantity })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast('Added to cart!', 'success');
        } else {
            if (data.redirect) window.location.href = data.redirect;
            else showToast(data.message || 'Error', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'));
}

function updateCartItem(cartId, quantity) {
    fetch(BASE_URL + '/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', cart_id: cartId, quantity: quantity })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else showToast(data.message, 'danger');
    });
}

function removeCartItem(cartId) {
    if (!confirm('Remove this item from cart?')) return;
    fetch(BASE_URL + '/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', cart_id: cartId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'alert alert-' + type + ' position-fixed top-0 end-0 m-3 shadow';
    toast.style.zIndex = '9999';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

// Bind add-to-cart buttons
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = parseInt(this.dataset.productId);
            const qty = parseInt(document.getElementById('quantity')?.value || 1);
            addToCart(id, qty);
        });
    });
});
