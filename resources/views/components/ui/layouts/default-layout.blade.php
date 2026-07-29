<!DOCTYPE html>
<html class="h-full bg-white" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config("app.name", "Laravel") }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('vendor/stickleapp/core/favicon.svg') }}" />

    {{-- Stickle's own assets, published by `vendor:publish --tag=package-assets`. --}}
    {{ app('stickle.vite') }}

    <!-- Alpine.js -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.8/cdn.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Simple-DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/8.4/pusher.min.js"></script>

    <!-- Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.0.2/dist/echo.iife.min.js"></script>

    <script>
        window.Pusher = Pusher;

        // An application that has not configured Reverb has no key to connect
        // with. Leaving window.Echo undefined rather than constructing a client
        // that cannot connect is what lets stickleStream() fall back to polling.
        if ('{{ config("broadcasting.connections.reverb.key") }}') {
            window.Echo = new Echo({
                broadcaster: "reverb",
                key: '{{ config("broadcasting.connections.reverb.key") }}',
                wsHost: '{{ config("broadcasting.connections.reverb.options.host") }}',
                wsPort: '{{ config("broadcasting.connections.reverb.options.port") }}',
                wssPort:
                    '{{ config("broadcasting.connections.reverb.options.port") }}',
                forceTLS: true,
                enabledTransports: ["ws", "wss"],
            });
        }
    </script>

    <!-- Relative times -->
    <script>
        /**
         * Formats a record's timestamp as "5m ago".
         *
         * Timestamps cross the wire as ISO-8601 UTC, which Carbon normalises to
         * even when the application runs on another timezone, so the browser is
         * free to render them wherever the viewer happens to be.
         */
        window.stickleTimeAgo = function (timestamp) {
            if (!timestamp) {
                return "just now";
            }

            const activityTime = new Date(timestamp);

            if (Number.isNaN(activityTime.getTime())) {
                return "just now";
            }

            // A record can land a second or two ahead of the browser when the
            // two clocks disagree, which would otherwise read as a negative age.
            const diffSeconds = Math.max(
                0,
                Math.floor((new Date() - activityTime) / 1000)
            );
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);

            if (diffSeconds < 60) {
                return `${diffSeconds}s ago`;
            } else if (diffMinutes < 60) {
                return `${diffMinutes}m ago`;
            } else if (diffHours < 24) {
                return `${diffHours}h ago`;
            }

            return `${Math.floor(diffHours / 24)}d ago`;
        };
    </script>

    <!-- Live updates: websocket where available, polling where not -->
    <script>
        /**
         * Subscribes to a channel and/or polls the requests endpoint, handing
         * both to the same callback.
         *
         * Reverb speaks only the Pusher WebSocket protocol -- it does not serve
         * the HTTP fallback endpoints pusher-js needs to degrade to long polling
         * on its own -- so the fallback lives here instead. We poll whenever the
         * socket is down and stop again as soon as it reconnects.
         *
         * Records are handed over newest first in the shape of RequestResource,
         * which is also what the broadcast payload carries.
         */
        window.stickleStream = function ({
            channel = null,
            endpoint = null,
            intervalSeconds = 15,
            pollingEnabled = true,
            onRecords = () => {},
        }) {
            const canPoll = pollingEnabled && Boolean(endpoint);
            const seen = new Set();
            let latestTimestamp = null;
            let timer = null;

            const keyFor = (record) =>
                [
                    record.type,
                    record.model_class,
                    record.object_uid,
                    record.session_uid,
                    record.timestamp,
                ].join("|");

            // start_at is inclusive, and a poll may overlap the websocket, so
            // both transports are filtered through the same set of seen keys.
            const remember = (records) =>
                records.filter((record) => {
                    const key = keyFor(record);

                    if (seen.has(key)) {
                        return false;
                    }

                    seen.add(key);

                    if (
                        record.timestamp &&
                        (!latestTimestamp || record.timestamp > latestTimestamp)
                    ) {
                        latestTimestamp = record.timestamp;
                    }

                    return true;
                });

            // The components cap their own lists at 100, so the keys behind them
            // are bounded too rather than growing for the life of the page.
            const forget = () => {
                if (seen.size <= 500) {
                    return;
                }

                [...seen]
                    .slice(0, seen.size - 500)
                    .forEach((key) => seen.delete(key));
            };

            const deliver = (records) => {
                const fresh = remember(records);

                forget();

                if (fresh.length) {
                    onRecords(fresh);
                }
            };

            const poll = async () => {
                if (document.hidden) {
                    return;
                }

                const url = new URL(endpoint, window.location.origin);

                if (latestTimestamp) {
                    url.searchParams.set("start_at", latestTimestamp);
                }

                try {
                    const response = await fetch(url, {
                        headers: { Accept: "application/json" },
                    });
                    const body = await response.json();

                    deliver(body.data ?? []);
                } catch (error) {
                    console.error("Error polling for updates:", error);
                }
            };

            const startPolling = () => {
                if (!canPoll || timer) {
                    return;
                }

                timer = setInterval(poll, intervalSeconds * 1000);
                poll();
            };

            const stopPolling = () => {
                if (!timer) {
                    return;
                }

                clearInterval(timer);
                timer = null;
            };

            // "connecting" and "initialized" are the states a healthy socket
            // passes through on the way up, so polling waits for one of the
            // states pusher-js reports once it has actually given up.
            const isSocketDown = (state) =>
                ["unavailable", "failed", "disconnected"].includes(state);

            if (window.Echo && channel) {
                window.Echo.channel(channel).listenToAll((eventName, data) => {
                    if (data && data.payload) {
                        deliver([data.payload]);
                    }
                });

                const connection = window.Echo.connector?.pusher?.connection;

                if (connection) {
                    connection.bind("state_change", ({ current }) => {
                        if (isSocketDown(current)) {
                            startPolling();
                        } else if (current === "connected") {
                            stopPolling();
                        }
                    });
                }
            }

            // Intervals are throttled in background tabs, so catch up on return
            // rather than waiting out the remainder of the current one.
            document.addEventListener("visibilitychange", () => {
                if (!document.hidden && timer) {
                    poll();
                }
            });

            return {
                // Records the caller already loaded itself, so the first poll
                // asks for what came after them instead of repeating them.
                seed(records) {
                    remember(records ?? []);
                },
                start() {
                    const connection = window.Echo?.connector?.pusher?.connection;

                    // Nothing is listening on a socket, or one is already known
                    // to be down. Anything else is left to state_change, so a
                    // healthy page load costs no extra request.
                    if (!window.Echo || !channel || !connection || isSocketDown(connection.state)) {
                        startPolling();
                    }
                },
                stop: stopPolling,
            };
        };
    </script>
    @stack('scripts')
</head>

<body class="h-full">
    {{-- Toast Notifications --}}
    <x-stickle::ui.primitives.toast position="top-right" />

    <div x-data="{ isOpen: false }">
        @include('stickle::components.ui.layouts.partials.menu', ['models => $models()'])

        <!-- Layout wrapper -->
        <div class="relative isolate flex min-h-svh w-full bg-white max-lg:flex-col lg:bg-zinc-100">

            <!-- Sidebar (desktop) -->
            @include('stickle::components.ui.layouts.partials.sidebar', ['models => $models()'])

            <!-- Mobile header -->
            @include('stickle::components.ui.layouts.partials.header')

            <!-- Main content -->
            <main class="flex flex-1 flex-col pb-2 lg:min-w-0 lg:pt-2 lg:pr-2 lg:pl-64">
                <div class="grow p-6 lg:rounded-lg lg:bg-white lg:p-10 lg:shadow-xs lg:ring-1 lg:ring-zinc-950/5">
                    {{-- Constrained but left-aligned against the sidebar. Was
                         `mx-auto max-w-6xl`; dropping mx-auto keeps the reading
                         width without centring it in dead space. --}}
                    <div class="max-w-6xl">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
@stack('drawers')

</html>
