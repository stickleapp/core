<!-- Leaflet CSS -->
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<!-- Map container -->
<div
    id="live-map"
    x-data="mapData()"
    class="w-full h-96 rounded-lg border border-gray-200"
></div>

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    function mapData() {
        return {
            async init() {
                // Initialize Leaflet map with world view
                const map = L.map("live-map", {
                    center: [20, 0], // Center of world
                    zoom: 2, // Show whole world
                    zoomControl: true,
                    scrollWheelZoom: true,
                });

                // Add OpenStreetMap tiles
                L.tileLayer(
                    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    {
                        attribution: "© OpenStreetMap contributors",
                        maxZoom: 18,
                    }
                ).addTo(map);

                // Add a simple red dot to show Alpine.js can access the map
                L.circleMarker([40.7128, -74.006], {
                    color: "red",
                    fillColor: "#f03",
                    fillOpacity: 0.8,
                    radius: 8,
                })
                    .addTo(map)
                    .bindPopup("NYC - Added via Alpine.js!");
            },
        };
    }
</script>
