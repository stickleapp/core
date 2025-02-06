<div>
    <div class="p-5">{{ $title }}</div>
    <canvas id="chart-canvas"></canvas>
</div>
<script>
    document.addEventListener("DOMContentLoaded", async function() {

        const endpoint = "{!! $endpoint !!}";
        
        try {
            const headers = new Headers({
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }) ;
            
            const response = await fetch(
                endpoint, 
                { 
                    headers
                }
            );

            const data = await response.json();
            
            // const labels = data.labels;

            // const dataset = data.values;

            const labels = ['','','','','',''];



            const ctx = document.getElementById("chart-canvas").getContext("2d");

            new Chart(
                ctx, 
                {
                    type: '{{ $type  }}',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: [12, 19, 3, 5, 2, 3],
                            backgroundColor: 'rgba(75, 192, 192, .7 )',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            fill: true,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                display: false,
                                grid: { drawTicks: false, drawBorder: false, drawOnChartArea: false }
                            },
                            y: {
                                display: false,
                                grid: { drawTicks: false, drawBorder: false, drawOnChartArea: false }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        layout: { padding: 0 }
                    }
                }
            );
        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
    });
</script>