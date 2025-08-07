// dashboard.js
// Charts initialization
document.addEventListener('DOMContentLoaded', function() {
    // Rent Collection Chart
    const rentCtx = document.getElementById('rentChart');
    if (rentCtx) {
        new Chart(rentCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Rent Collected',
                    data: [16500, 16800, 17200, 17500, 17800, 18000, 18200, 18500, 18800, 18420],
                    backgroundColor: 'rgba(67, 97, 238, 0.7)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart');
    if (occupancyCtx) {
        new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Vacant', 'Under Maintenance'],
                datasets: [{
                    data: [85, 10, 5],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(243, 156, 18, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
});

// Tab switching
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const parent = this.parentElement;
        parent.querySelectorAll('.tab').forEach(t => {
            t.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Button hover effects
document.querySelectorAll('.btn, .action-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Search functionality
const searchInput = document.querySelector('.search-bar input');
searchInput?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    
    document.querySelectorAll('table tbody tr').forEach(row => {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(searchTerm) ? '' : 'none';
    });
});

// Check login state when dashboard loads
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('isLoggedIn') !== 'true') {
        // If not logged in, redirect to login page
        window.location.href = 'login.html';
    }
});

// Logout functionality
document.querySelectorAll('#logout-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        localStorage.removeItem('isLoggedIn');
        window.location.href = 'login.html';
    });
});