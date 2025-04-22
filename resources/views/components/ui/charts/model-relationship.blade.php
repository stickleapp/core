<div x-data="chartData{{ md5($endpoint) }}()">
    <div
        class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8 shadow-sm"
    >
        <dt class="text-sm/6 font-medium text-gray-500">{{ $label }}</dt>
        <dd>@include('stickle::components.ui.charts.primatives.delta')</dd>
        @if($currentValue)
        <dd
            class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900"
        >
            {{ $currentValue }}
        </dd>
        @endif
        <div>
            <canvas x-ref="{{ $key }}" id="{{ $key }}"></canvas>
        </div>
    </div>
</div>

<script>
    function chartData{{ md5($endpoint) }}() {
        // Declare 'chart' with 'let' to prevent it from being reactive in Alpine.js.
        // This is because Chart.js manipulates the DOM directly, which can conflict with Alpine.js's reactivity.
        let chart;

        const clearChartData = () => {
            chart.data.labels.length = 0;
            chart.data.datasets.forEach(dataset => {
                dataset.data.length = 0;
            });
        };

        const setChartData = (data) => {
            chart.data.labels = data.time_series.map(row => row.timestamp);
            chart.data.datasets[0].data = data.time_series.map(row => row.value);
        };

        const fetchChartData = async () => {
            this.isLoading = true;
            return await fetch("{!! $endpoint !!}")
                .then((response) => response.json())
                .then((data) => {
                    return data;
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
            delta: null,
            async init() {
                const data = await fetchChartData();
                this.delta = data.delta;
                if (!data) return;
                this.renderChart(data);
            },
            async updateChart() {
                clearChartData();
                const data = await fetchChartData();
                this.delta = data.delta;
                if (!data) return;
                setChartData(data);
                chart.update();
            },
            async renderChart(data) {
                chart = new Chart(this.$refs['{{ $key }}'], {
                    type: "line",
                    data: {
                        labels: data.data.map(row => row.recorded_at),
                        datasets: [
                            {
                                data: data.data.map(row => row.value_avg),
                                backgroundColor: "rgba(250, 204, 21, .7)",
                                borderColor: "rgba(250, 204, 21, .7)",
                                borderWidth: 2,
                                fill: false,

                                pointRadius: 2, // Size of the points (adjust as needed)
                                pointBackgroundColor: "white", // White center
                                pointBorderColor: "rgba(250, 204, 21, .7)", // Same as line color
                                pointBorderWidth: 1, // Border thickness
                                pointHoverRadius: 2, // Slightly larger on hover
                                pointHoverBackgroundColor: "white",
                                pointHoverBorderColor: "rgba(250, 204, 21, 1)", // Full opacity on hover
                                pointHoverBorderWidth: 1,

                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                display: false,
                                grid: {
                                    drawTicks: false,
                                    drawBorder: false,
                                    drawOnChartArea: false,
                                },
                            },
                            y: {
                                display: false,
                                grid: {
                                    drawTicks: false,
                                    drawBorder: false,
                                    drawOnChartArea: false,
                                },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        layout: { padding: 0 },
                    }
                });
            }
        }
    }
</script>
