<div x-data="chartData{{ md5($endpoint) }}()" x-init="query()">
    <div class="text-2xl font-bold" x-text="value"></div>
    <p class="text-xs text-muted-foreground"></p>
</div>

<script>
    function chartData{{ md5($endpoint) }}() {
        return {
            value: null,
            init() {
                this.query();
            },
            query() {
                fetch("{!! $endpoint !!}")
                    .then((response) => response.json())
                    .then((data) => {
                        // Update the 'value' property
                        this.value = data[0].avg ?? "-";

                        console.log("Value after assignment:", this.value2);
                        // Alternatively, you can use
                        // this.value = data;
                    })
                    .catch((error) => {
                        console.error("Error fetching data:", error);
                    });
            },
        };
    }
</script>
