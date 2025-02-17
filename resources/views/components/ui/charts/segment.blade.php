<div>
  <div class="sm:flex sm:items-center mb-8">
      <div class="sm:flex-auto p-6">
          <h1 class="text-base font-semibold text-gray-900">Users</h1>
          <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title, email and role.</p>
      </div>
  </div>  
  <canvas id="chart-canvas"></canvas>
</div>

<script>
    document.addEventListener("DOMContentLoaded", async function() {
        const endpoint = "{!! $endpoint !!}";
        
            const headers = new Headers({
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }) ;
            
        try {
            const response = await fetch(
                endpoint, 
                { 
                    headers
                }
            );

        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
            // const data = await response.json();
            
            // const labels = data.labels;

            // const dataset = data.values;

            const labels = ['','','','','',''];



            const ctx = document.getElementById("chart-canvas").getContext("2d");

            new Chart(
                ctx, 
                {
                    type: 'bar',
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
    });
</script>