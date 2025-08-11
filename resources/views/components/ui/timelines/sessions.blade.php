<div x-data="sessionTimelineData()">
    <!-- Session counter -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            <span id="session-count" class="text-sm font-medium text-gray-900"
                >0 active sessions</span
            >
        </div>
        <div class="text-xs text-gray-500">Live updates</div>
    </div>

    <!-- Sessions list -->
    <div id="sessions-container" class="space-y-3 max-h-80 overflow-y-auto">
        <!-- Placeholder content -->
        <div class="text-center py-8 text-gray-500">
            <p class="text-sm">No active sessions</p>
            <p class="text-xs text-gray-400 mt-1">
                Sessions will appear here when users are online
            </p>
        </div>
        <ul id="sessions-container" role="list" class="-mb-8"></ul>
    </div>
</div>

<script>
    function sessionTimelineData() {
        const activities = [];
        const sessionsContainer = document.getElementById("sessions-container");
        const sessionCount = document.getElementById("session-count");

        function formatTimeAgo(timestamp) {
            const now = new Date();
            const activityTime = new Date(timestamp);
            const diffMs = now - activityTime;
            const diffSeconds = Math.floor(diffMs / 1000);
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (diffSeconds < 60) {
                return `${diffSeconds}s ago`;
            } else if (diffMinutes < 60) {
                return `${diffMinutes}m ago`;
            } else if (diffHours < 24) {
                return `${diffHours}h ago`;
            } else {
                return `${diffDays}d ago`;
            }
        }

        function renderSessions() {
            // Group activities by model_class and object_uid, keeping the most recent
            const uniqueSessions = activities.reduce((acc, activity) => {
                const key = `${activity.model_class}_${activity.object_uid}`;
                if (
                    !acc[key] ||
                    new Date(activity.timestamp) > new Date(acc[key].timestamp)
                ) {
                    acc[key] = activity;
                }
                return acc;
            }, {});

            const sessionsList = Object.values(uniqueSessions);

            if (sessionsList.length === 0) {
                sessionCount.textContent = "0 active sessions";
                return;
            }

            sessionsContainer.innerHTML = "";
            sessionCount.textContent = `${sessionsList.length} active session${
                sessionsList.length !== 1 ? "s" : ""
            }`;

            sessionsList.forEach((session, index) => {
                const sessionElement = document.createElement("div");
                sessionElement.className =
                    "flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer";

                // Add click handler to emit user selection event
                sessionElement.addEventListener("click", function () {
                    const event = new CustomEvent("user-selected", {
                        detail: session,
                        bubbles: true,
                    });
                    sessionElement.dispatchEvent(event);
                });

                const timeAgo = formatTimeAgo(session.timestamp);
                const locationText = session.location?.city
                    ? `${session.location.city}${
                          session.location.state
                              ? ", " + session.location.state
                              : ""
                      }`
                    : "Unknown location";
                const userLabel = session.model?.label || "Unknown User";
                const userInitial = userLabel.charAt(0).toUpperCase();

                sessionElement.innerHTML = `
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                            ${userInitial}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            ${
                                session.model?.url
                                    ? `<a href="${session.model.url}" class="hover:text-blue-600">${userLabel}</a>`
                                    : userLabel
                            }
                        </p>
                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                            <span>${locationText}</span>
                            <span>•</span>
                            <span>${timeAgo}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    </div>
                `;

                sessionsContainer.appendChild(sessionElement);
            });
        }

        const fetchData = async () => {
            this.isLoading = true;
            return await fetch("{!! $activitiesEndpoint !!}")
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

        // Update listener for Echo
        if (window.Echo) {
            window.Echo.channel("{{ $channel }}").listenToAll(
                (eventName, data) => {
                    // Add new activity from websocket
                    if (data && data.activity) {
                        activities.unshift(data.activity);

                        // Keep only the most recent 100 activities to prevent memory issues
                        if (activities.length > 100) {
                            activities.splice(100);
                        }

                        renderSessions();
                    }
                }
            );
        }

        return {
            isLoading: false,
            modelAttributes: {},
            async init() {
                const data = await fetchData();
                if (!data) return;
                activities.push(...data);
                renderSessions();
            },
        };
    }
</script>
