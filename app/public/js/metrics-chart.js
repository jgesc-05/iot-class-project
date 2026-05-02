window.metricsChart = function(deviceId, unit) {
    // Variables PRIVADAS (closure scope) - NO entran al state reactivo de Alpine.
    // Esto evita que Alpine envuelva la instancia de Chart en un Proxy,
    // lo que causaba "Maximum call stack size exceeded".
    let chartInstance = null;
    let intervalId = null;

    return {
        // Solo las propiedades que la VISTA usa son reactivas.
        hasData: false,
        lastUpdate: '',

        init() {
            if (chartInstance) {
                return;
            }

            const canvas = this.$refs.canvas;
            if (!canvas) {
                console.warn('Canvas ref not found');
                return;
            }

            const existing = Chart.getChart(canvas);
            if (existing) {
                existing.destroy();
            }

            const ctx = canvas.getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: unit,
                        data: [],
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(c) {
                                    return c.parsed.y.toFixed(2) + ' ' + unit;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { maxTicksLimit: 6, maxRotation: 0 },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        }
                    }
                }
            });

            this.fetchData();
            intervalId = setInterval(() => this.fetchData(), 5000);
        },

        async fetchData() {
            try {
                const res = await fetch('/api/devices/' + deviceId + '/metrics');
                if (!res.ok) {
                    console.error('Error fetching metrics:', res.status);
                    return;
                }
                const data = await res.json();
                this.updateChart(data.metrics);
                this.lastUpdate = 'Actualizado ' + new Date().toLocaleTimeString();
            } catch (e) {
                console.error('Network error fetching metrics:', e);
            }
        },

        updateChart(metrics) {
            if (!chartInstance) return;

            if (!metrics || metrics.length === 0) {
                this.hasData = false;
                chartInstance.data.labels = [];
                chartInstance.data.datasets[0].data = [];
                chartInstance.update('none');
                return;
            }

            this.hasData = true;
            chartInstance.data.labels = metrics.map(function(m) {
                const d = new Date(m.time);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            });
            chartInstance.data.datasets[0].data = metrics.map(function(m) {
                return m.value;
            });
            chartInstance.update('none');
        }
    };
};
