/**
 * Elegance Sarees - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash alerts after 5 seconds
    document.querySelectorAll('.flash-alert').forEach(function (el) {
        setTimeout(function () {
            var alert = bootstrap.Alert.getOrCreateInstance(el);
            if (alert) alert.close();
        }, 5000);
    });
});
