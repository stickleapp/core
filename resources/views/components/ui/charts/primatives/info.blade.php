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
                        this.value = data[0].value_avg ?? "-";
                    })
                    .catch((error) => {
                        console.error("Error fetching data:", error);
                    });
            },
        };
    }
</script>
