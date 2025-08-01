<div x-data="chartData{{ md5($endpoint) }}()">
    <div
        class="gap-x-4 gap-y-2 bg-white shadow-sm overflow-hidden sm:rounded-lg"
    >
        <div
            class="flex flex-wrap items-baseline justify-between px-4 py-10 sm:px-6 xl:px-8"
        >
            <dt class="text-sm/6 font-medium text-gray-500">{{ $label }}</dt>
            <dd>@include('stickle::components.ui.charts.primatives.delta')</dd>
            <dd
                class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900"
                x-text="currentValue"
            ></dd>
        </div>
        <div class="w-full" style="height: 150px">
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
            currentValue: null,
            async init() {
                const data = await fetchChartData();
                if (!data) return;
                this.setDeltaValue(data);
                this.setCurrentValue(data);
                this.renderChart(data);
            },
            async updateChart() {
                clearChartData();
                const data = await fetchChartData();
                this.delta = data.delta;
                if (!data) return;
                this.setDeltaValue(data);
                this.setCurrentValue(data);
                setChartData(data);
                chart.update();
            },
            setCurrentValue(data) {
                if (data.time_series && data.time_series.length > 0) {
                    let value = data.time_series[data.time_series.length - 1].value;
                    this.currentValue = Math.round(value);
                }
            },
            setDeltaValue(data) {
                this.delta = data.delta;
            },
            async renderChart(data) {

                // Create gradient for the chart fill
                const ctx = this.$refs['{{ $key }}'].getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 150);
                gradient.addColorStop(0, 'rgba(250, 204, 21, 0.7)');
                gradient.addColorStop(1, 'rgba(250, 204, 21, 0.1)');

                chart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: data.time_series.map(row => row.timestamp),
                        datasets: [
                            {
                                data: data.time_series.map(row => row.value),
                                backgroundColor: gradient,
                                borderColor: "rgba(250, 204, 21, .7)",
                                borderWidth: 2,
                                fill: true,

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
                        responsive: true,
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
