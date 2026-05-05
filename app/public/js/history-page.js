window.historyPage = function() {
    return {
        // State reactivo (lo que muestra la UI).
        loading: false,
        hasData: false,
        stats: {
            avg: null,
            min: null,
            max: null,
            count: 0,
        },
        unit: '',

        init() {
            // Escuchar cambios desde Livewire (cuando cambia el device o el rango).
            this.$wire.$watch('deviceId', () => this.fetchStats());
            this.$wire.$watch('fromDate', () => this.fetchStats());
            this.$wire.$watch('toDate', () => this.fetchStats());

            // Primer fetch al cargar (si ya hay device).
            this.fetchStats();
        },

        async fetchStats() {
            const deviceId = this.$wire.deviceId;
            const fromDate = this.$wire.fromDate;
            const toDate = this.$wire.toDate;

            if (!deviceId || !fromDate || !toDate) {
                this.hasData = false;
                return;
            }

            this.loading = true;

            try {
                // Convertir formato HTML5 datetime-local (Y-m-d\TH:i) a ISO 8601.
                const fromIso = new Date(fromDate).toISOString();
                const toIso = new Date(toDate).toISOString();

                const url = '/api/devices/' + deviceId + '/stats'
                    + '?from=' + encodeURIComponent(fromIso)
                    + '&to=' + encodeURIComponent(toIso);

                const res = await fetch(url);
                if (!res.ok) {
                    console.error('Error fetching stats:', res.status);
                    this.hasData = false;
                    return;
                }

                const data = await res.json();
                this.stats = data.stats;
                this.hasData = data.stats.count > 0;

                // Leer la unidad desde la propiedad publica del componente Livewire.
                this.unit = this.$wire.unit || '';
            } catch (e) {
                console.error('Network error fetching stats:', e);
                this.hasData = false;
            } finally {
                this.loading = false;
            }
        },

        format(value) {
            if (value === null || value === undefined) return '—';
            return Number(value).toFixed(2);
        }
    };
};
