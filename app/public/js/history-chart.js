window.historyChart = function() {
    // Variables PRIVADAS (closure scope) - NO entran al state reactivo de Alpine.
    let chartInstance = null;

    return {
        loading: false,
        hasData: false,
        bucketLabel: '',
        pointCount: 0,

        init() {
            // Inicializar el chart cuando el componente Alpine arranca.
            const canvas = this.$refs.canvas;
            if (!canvas) return;

            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            const ctx = canvas.getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Mínimo',
                            data: [],
                            borderColor: 'rgba(59, 130, 246, 0.6)',
                            backgroundColor: 'rgba(99, 102, 241, 0.05)',
                            borderWidth: 1,
                            pointRadius: 0,
                            tension: 0.3,
                            fill: '+2',
                        },
                        {
                            label: 'Promedio',
                            data: [],
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.3,
                            fill: false,
                        },
                        {
                            label: 'Máximo',
                            data: [],
                            borderColor: 'rgba(239, 68, 68, 0.6)',
                            backgroundColor: 'rgba(0, 0, 0, 0)',
                            borderWidth: 1,
                            pointRadius: 0,
                            tension: 0.3,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { boxWidth: 12, font: { size: 11 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { maxTicksLimit: 8, maxRotation: 0 },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        }
                    }
                }
            });

            // Watches de Livewire.
            this.$wire.$watch('deviceId', () => this.fetchHistory());
            this.$wire.$watch('fromDate', () => this.fetchHistory());
            this.$wire.$watch('toDate', () => this.fetchHistory());

            this.fetchHistory();
        },

        async fetchHistory() {
            const deviceId = this.$wire.deviceId;
            const fromDate = this.$wire.fromDate;
            const toDate = this.$wire.toDate;

            if (!deviceId || !fromDate || !toDate) {
                this.hasData = false;
                this.clearChart();
                return;
            }

            this.loading = true;

            try {
                const fromIso = new Date(fromDate).toISOString();
                const toIso = new Date(toDate).toISOString();

                const url = '/api/devices/' + deviceId + '/history'
                    + '?from=' + encodeURIComponent(fromIso)
                    + '&to=' + encodeURIComponent(toIso)
                    + '&bucket=auto';

                const res = await fetch(url);
                if (!res.ok) {
                    console.error('Error fetching history:', res.status);
                    this.hasData = false;
                    return;
                }

                const data = await res.json();
                this.bucketLabel = data.bucket || '';
                this.pointCount = data.count || 0;
                this.updateChart(data.history);
            } catch (e) {
                console.error('Network error fetching history:', e);
                this.hasData = false;
            } finally {
                this.loading = false;
            }
        },

        updateChart(history) {
            if (!chartInstance) return;

            if (!history || history.length === 0) {
                this.hasData = false;
                this.clearChart();
                return;
            }

            this.hasData = true;
            chartInstance.data.labels = history.map(function(h) {
                // Backend devuelve "2026-04-29 02:00:00+00" - normalizar a ISO 8601
                const isoTime = h.time.replace(' ', 'T').replace(/\+(\d{2})$/, '+$1:00');
                const d = new Date(isoTime);
                return d.toLocaleString([], {
                    month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            });
            chartInstance.data.datasets[0].data = history.map(h => h.min);
            chartInstance.data.datasets[1].data = history.map(h => h.avg);
            chartInstance.data.datasets[2].data = history.map(h => h.max);
            chartInstance.update('none');
        },

        clearChart() {
            if (!chartInstance) return;
            chartInstance.data.labels = [];
            chartInstance.data.datasets.forEach(ds => ds.data = []);
            chartInstance.update('none');
        }
    };
};
