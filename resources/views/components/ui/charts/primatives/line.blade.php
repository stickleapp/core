<canvas id="{{ $key }}" style="width: 100%"></canvas>

<script>
    // const labels = $apiResponse.map((item) => item.timestamp);

    // const dataSet1Data = $apiResponse.map((item) => item.value);

    const ctx = document.getElementById("{{ $key }}").getContext("2d");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: {{ $labels }},
            datasets: [
                {
                    data: dataSet1Data,
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
        },
    });
</script>
