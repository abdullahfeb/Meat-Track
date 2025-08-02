document.addEventListener('DOMContentLoaded', () => {
    const charts = {
        inventory: null,
        spoilage: null,
        distribution: null
    };

    function initializeCharts(data) {
        const ctxInventory = document.getElementById('inventoryChart')?.getContext('2d');
        const ctxSpoilage = document.getElementById('spoilageChart')?.getContext('2d');
        const ctxDistribution = document.getElementById('distributionChart')?.getContext('2d');

        if (ctxInventory) {
            charts.inventory = new Chart(ctxInventory, {
                type: 'pie',
                data: {
                    labels: data.inventory.map(item => item.status),
                    datasets: [{
                        data: data.inventory.map(item => item.count),
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        if (ctxSpoilage) {
            charts.spoilage = new Chart(ctxSpoilage, {
                type: 'bar',
                data: {
                    labels: data.spoilage.map(item => item.meat_type),
                    datasets: [{
                        label: 'Spoilage (kg)',
                        data: data.spoilage.map(item => item.total_quantity),
                        backgroundColor: '#e74a3b'
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        if (ctxDistribution) {
            charts.distribution = new Chart(ctxDistribution, {
                type: 'doughnut',
                data: {
                    labels: data.distribution.map(item => item.status),
                    datasets: [{
                        data: data.distribution.map(item => item.count),
                        backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    }

    function fetchDashboardData() {
        fetch('dashboard_data.php')
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    initializeCharts(data.data);
                } else {
                    console.error('Error fetching dashboard data:', data.message);
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    fetchDashboardData();
});