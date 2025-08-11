<div
    class="w-full h-96 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 overflow-hidden"
>
    <div class="relative h-full flex items-center justify-center">
        <!-- Placeholder map visualization -->
        <div class="text-center space-y-4">
            <div
                class="w-16 h-16 bg-blue-500 rounded-full mx-auto flex items-center justify-center animate-pulse"
            >
                <svg
                    class="w-8 h-8 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
            </div>
            <div class="text-blue-600 font-medium">
                Interactive Map Loading...
            </div>
            <div class="text-sm text-gray-500">
                Real-time user locations and activity will appear here
            </div>
        </div>

        <!-- Sample location markers with click handlers -->
        <div
            class="absolute top-1/4 left-1/3 w-3 h-3 bg-green-500 rounded-full border-2 border-white shadow-lg animate-ping cursor-pointer hover:scale-150 transition-transform"
            data-city="New York"
            data-country="USA"
            title="New York, USA - Click to filter"
        ></div>
        <div
            class="absolute top-1/2 right-1/4 w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-lg animate-pulse cursor-pointer hover:scale-150 transition-transform"
            data-city="London"
            data-country="UK"
            title="London, UK - Click to filter"
        ></div>
        <div
            class="absolute bottom-1/3 left-1/2 w-3 h-3 bg-yellow-500 rounded-full border-2 border-white shadow-lg animate-bounce cursor-pointer hover:scale-150 transition-transform"
            data-city="Tokyo"
            data-country="Japan"
            title="Tokyo, Japan - Click to filter"
        ></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log(
            "Live map component loaded - ready for integration with mapping service"
        );
        
        // Add click handlers to location markers
        const markers = document.querySelectorAll('[data-city][data-country]');
        markers.forEach(marker => {
            marker.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const city = this.getAttribute('data-city');
                const country = this.getAttribute('data-country');
                
                const event = new CustomEvent('area-clicked', {
                    detail: { city, country },
                    bubbles: true
                });
                
                this.dispatchEvent(event);
            });
        });
        
        // Future integration point for real mapping service (Mapbox, Google Maps, etc.)
    });
</script>
