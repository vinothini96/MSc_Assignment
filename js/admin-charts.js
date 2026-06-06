/**
 * Admin dashboard charts (Chart.js)
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || !window.adminChartData) return;

    var data = window.adminChartData;

    var salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: data.months,
                datasets: [{
                    label: 'Sales (Rs.)',
                    data: data.sales,
                    backgroundColor: 'rgba(139, 34, 82, 0.7)',
                    borderColor: 'rgba(107, 26, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (v) { return 'Rs.' + v.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    var statusCtx = document.getElementById('statusChart');
    if (statusCtx && data.statuses.length) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: data.statuses,
                datasets: [{
                    data: data.statusCounts,
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(32, 201, 151, 0.8)',
                        'rgba(139, 34, 82, 0.8)',
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: { responsive: true }
        });
    }
});
