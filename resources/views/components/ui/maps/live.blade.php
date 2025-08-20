<!-- Leaflet CSS -->
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>
    .custom-marker {
        background: none !important;
        border: none !important;
    }

    .custom-marker div {
        animation: pulse-marker 2s infinite;
    }

    @keyframes pulse-marker {
        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
        }
    }
</style>

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
        const requests = [];
        const sessionMarkers = new Map();
        let map = null;

        function formatTimeAgo(timestamp) {
            const now = new Date();
            const activityTime = new Date(timestamp);
            const diffMs = now - activityTime;
            const diffSeconds = Math.floor(diffMs / 1000);
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);

            if (diffSeconds < 60) {
                return `${diffSeconds}s ago`;
            } else if (diffMinutes < 60) {
                return `${diffMinutes}m ago`;
            } else if (diffHours < 24) {
                return `${diffHours}h ago`;
            } else {
                return `${Math.floor(diffHours / 24)}d ago`;
            }
        }

        function createSessionMarker(session) {
            if (
                !session.location_data?.coordinates?.lat ||
                !session.location_data?.coordinates?.lng
            ) {
                return null;
            }

            const userLabel = session.model?.label || "Unknown User";
            const locationText = session.location_data?.city
                ? `${session.location_data.city}${
                      session.location_data.state
                          ? ", " + session.location_data.state
                          : ""
                  }`
                : "Unknown location";
            const timeAgo = formatTimeAgo(session.timestamp);

            // Create a custom icon with user initial
            const userInitial = userLabel.charAt(0).toUpperCase();
            const iconHtml = `
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs border-2 border-white shadow-lg">
                    ${userInitial}
                </div>
            `;

            const customIcon = L.divIcon({
                html: iconHtml,
                className: "custom-marker",
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16],
            });

            const marker = L.marker(
                [
                    session.location_data.coordinates.lat,
                    session.location_data.coordinates.lng,
                ],
                {
                    icon: customIcon,
                }
            );

            const popupContent = `
                <div class="text-sm">
                    <div class="font-semibold text-gray-900 mb-2">${userLabel}</div>
                    <div class="text-gray-600 space-y-1">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <span>${locationText}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <span>${timeAgo}</span>
                        </div>
                        ${
                            session.location_data.country
                                ? `<div class="text-xs text-gray-500">${session.location_data.country}</div>`
                                : ""
                        }
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            return marker;
        }

        function updateSessionMarkers() {
            // Clear existing markers
            sessionMarkers.forEach((marker) => {
                map.removeLayer(marker);
            });
            sessionMarkers.clear();

            // Group requests by model_class and object_uid, keeping the most recent
            const uniqueSessions = requests.reduce((acc, activity) => {
                const key = `${activity.model_class}_${activity.object_uid}`;
                if (
                    !acc[key] ||
                    new Date(activity.timestamp) > new Date(acc[key].timestamp)
                ) {
                    acc[key] = activity;
                }
                return acc;
            }, {});

            // Create markers for each unique session
            Object.values(uniqueSessions).forEach((session) => {
                const marker = createSessionMarker(session);
                if (marker) {
                    const sessionKey = `${session.model_class}_${session.object_uid}`;
                    sessionMarkers.set(sessionKey, marker);
                    marker.addTo(map);
                }
            });
        }

        const fetchData = async () => {
            this.isLoading = true;
            return await fetch("{!! $requestsEndpoint !!}")
                .then((response) => response.json())
                .then((data) => {
                    return data.data;
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
            async init() {
                // Initialize Leaflet map with world view
                map = L.map("live-map", {
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

                // Load initial session data
                const data = await fetchData();
                if (data && data.length > 0) {
                    requests.push(...data);
                    updateSessionMarkers();
                }

                // Set up websocket listener for real-time updates
                const channel = "{!! $channel !!}";
                if (window.Echo && channel) {
                    window.Echo.channel(channel).listenToAll(
                        (eventName, data) => {
                            if (data && data.payload) {
                                requests.unshift(data.payload);

                                // Keep only the most recent 100 requests to prevent memory issues
                                if (requests.length > 100) {
                                    requests.splice(100);
                                }

                                updateSessionMarkers();
                            }
                        }
                    );
                }
            },
        };
    }
</script>
