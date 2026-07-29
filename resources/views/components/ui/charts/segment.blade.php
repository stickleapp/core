<div x-data="chartData{{ md5($endpoint) }}()" class="h-full">
    <div
        class="h-full flex flex-col gap-x-4 gap-y-2 bg-white shadow-sm overflow-hidden sm:rounded-lg"
    >
        <div
            class="flex-1 flex flex-col px-4 py-6 sm:px-6 xl:px-8"
        >
            <dt class="text-sm/6 font-medium text-gray-500 truncate">{{ $label }}</dt>
            <dd
                class="text-3xl/10 font-medium tracking-tight text-gray-900 mt-1"
                x-text="currentValue"
            ></dd>
            <dd class="mt-1">@include('stickle::components.ui.charts.primatives.delta')</dd>
        </div>
        {{-- `block` matters: a canvas is inline by default, so it sits on the text
             baseline and leaves a few px of white below it inside the card. --}}
        <div class="w-full shrink-0" style="height: 112px">
            <canvas x-ref="{{ $key }}" id="{{ $key }}" class="block h-full w-full"></canvas>
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
                const gradient = ctx.createLinearGradient(0, 0, 0, 112);
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

                                // Let the line, fill and points draw past the chart
                                // area instead of being inset by their own radius
                                // and stroke width, so the series bleeds to the
                                // card edge. The card's overflow-hidden trims it
                                // to the rounded corners.
                                clip: false,

                                pointRadius: 2, // Size of the points (adjust as needed)
                                pointBackgroundColor: "white", // White center
                                pointBorderColor: "rgba(250, 204, 21, .7)", // Same as line color
                                pointBorderWidth: 1, // Border thickness
                                pointHoverRadius: 4, // Slightly larger on hover
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
                        // intersect:false means the nearest point along the x
                        // axis wins, so a 2px dot does not have to be hit exactly.
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                displayColors: false,
                                callbacks: {
                                    title: (items) => {
                                        const d = new Date(items[0].label);
                                        return isNaN(d) ? items[0].label : d.toLocaleDateString(undefined, {
                                            year: 'numeric', month: 'short', day: 'numeric',
                                        });
                                    },
                                    // formattedValue passes the raw value through
                                    // (54.806); cap the noise at two decimals.
                                    label: (item) => Number(item.parsed.y).toLocaleString(undefined, {
                                        maximumFractionDigits: 2,
                                    }),
                                },
                            },
                        },
                        // autoPadding:false is the one that matters. Chart.js
                        // otherwise insets the chart area by the largest point's
                        // radius + border (2 + 1 = 3px here) so points can't be
                        // clipped, which leaves a white margin inside the card.
                        // padding:0 alone does not override that, and neither
                        // does clip:false on the dataset.
                        layout: { padding: 0, autoPadding: false },
                    }
                });
            }
        }
    }
</script>
