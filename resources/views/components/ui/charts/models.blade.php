<div x-data="chartData{{ md5($endpoint) }}()">
    <div
        class="justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8 shadow-sm"
    >
        <h3 class="tracking-tight text-sm font-normal pb-2">{{ $label }}</h3>
        @include('stickle::components.ui.charts.primatives.info')
    </div>
</div>

<script>
    function chartData{{ md5($endpoint) }}() {

        const clearChartData = () => {

        };

        const setChartData = (data) => {

        };

        const fetchChartData = async () => {
            this.isLoading = true;
            return await fetch("{!! $endpoint !!}")
                .then((response) => response.json())
                .then((data) => {
                 return data[0] ?? {}
                })
                .catch((error) => {
                    console.error("Error fetching data:", error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        };

        return {
            isLoading: false,
            currentValue: null,
            async init() {
                const data = await fetchChartData();
                if (!data) return;
                this.setCurrentValue(data);
            },
            async updateChart() {
                clearChartData();
                const data = await fetchChartData();
                if (!data) return;
                this.setCurrentValue(data);
            },
            setCurrentValue(data) {
                this.currentValue = Math.round(data.value_avg);
            }
        }

    }
</script>
