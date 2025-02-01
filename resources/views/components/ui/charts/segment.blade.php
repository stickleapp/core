<div>
    <div>{{ $title }}</div>
    <canvas id="chart-canvas"></canvas>
    <div>{{ $endpoint }}</div>
    <div>{{ $attribute }}</div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", async function() {
        const endpoint = "{{ $endpoint }}";
        
        try {
            const response = await fetch(endpoint);
            const data = await response.json();
            
            // Assuming the API returns data in the format { labels: [], values: [] }
            const labels = data.labels;
            const values = data.values;

            const ctx = document.getElementById("chart-canvas").getContext("2d");
            new Chart(
                ctx, 
                {
                    type: '{{ $type }}',
                    data: {
                        labels: {{ Illuminate\Support\Js::from($labels) }},
                        datasets: [{
                            label: '# of Votes',
                            data: [12, 19, 3, 5, 2, 3],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                            beginAtZero: true
                            }
                        }   
                    }
                }
            );
        } catch (error) {
            console.error("Error fetching chart data:", error);
        }
    });
</script>