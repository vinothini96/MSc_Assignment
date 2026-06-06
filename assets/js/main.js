/**
 * Main JavaScript - search autocomplete, sliders, wishlist
 */

document.addEventListener('DOMContentLoaded', function () {
    // Hero slider auto-rotate
    const slides = document.querySelectorAll('.hero-slide-item');
    if (slides.length > 1) {
        let current = 0;
        slides.forEach((s, i) => s.style.display = i === 0 ? 'block' : 'none');
        setInterval(() => {
            slides[current].style.display = 'none';
            current = (current + 1) % slides.length;
            slides[current].style.display = 'block';
        }, 5000);
    }

    // Search autocomplete
    const searchInput = document.getElementById('searchInput');
    const suggestions = document.getElementById('searchSuggestions');
    let debounceTimer;

    if (searchInput && suggestions) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                suggestions.classList.remove('show');
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch(BASE_URL + '/api/search.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) {
                            suggestions.classList.remove('show');
                            return;
                        }
                        suggestions.innerHTML = data.map(p =>
                            `<a href="${BASE_URL}/product-detail.php?id=${p.id}">${p.name} - $${p.price}</a>`
                        ).join('');
                        suggestions.classList.add('show');
                    });
            }, 300);
        });

        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.remove('show');
            }
        });
    }

    // Star rating input highlight
    document.querySelectorAll('.rating-input label').forEach(label => {
        label.addEventListener('mouseenter', function () {
            const val = this.htmlFor.replace('star', '');
            highlightStars(val);
        });
    });
});

function highlightStars(upTo) {
    document.querySelectorAll('.rating-input label').forEach((l, i) => {
        l.style.color = (5 - i) <= upTo ? '#ff9900' : '#ccc';
    });
}

function toggleWishlist(productId, btn) {
    fetch(BASE_URL + '/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.redirect) { window.location.href = data.redirect; return; }
        if (data.success) {
            btn.classList.toggle('btn-danger', data.added);
            btn.classList.toggle('btn-outline-danger', !data.added);
            btn.innerHTML = data.added ? '<i class="bi bi-heart-fill"></i>' : '<i class="bi bi-heart"></i>';
        }
    });
}
