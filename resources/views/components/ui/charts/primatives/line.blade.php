<div x-data="chartData{{ md5($endpoint) }}()" x-init="query()">
    <canvas id="{{ $key }}" style="width: 100%"></canvas>
</div>

<script>
    function chartData{{ md5($endpoint) }}() {
        return {
            init() {
                this.query();
            },
            query() {
                let apiData;
                fetch("{!! $endpoint !!}")
                    .then((response) => response.json())
                    .then((data) => {
                        const apiResponse = data.data;

                        const labels = apiResponse.map(
                            (item) => item.recorded_at
                        );

                        const dataSet1Data = apiResponse.map(
                            (item) => item.value_avg
                        );

                        const ctx = document
                            .getElementById("{{ $key }}")
                            .getContext("2d");

                        new Chart(ctx, {
                            type: "line",
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        data: dataSet1Data,
                                        backgroundColor:
                                            "rgba(75, 192, 192, .7 )",
                                        borderColor: "rgba(75, 192, 192, 1)",
                                        borderWidth: 2,
                                        fill: true,
                                        pointRadius: 0,
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
                            },
                        });
                    })
                    .catch((error) => {
                        console.error("Error fetching data:", error);
                    });
            },
        };
    }
</script>
