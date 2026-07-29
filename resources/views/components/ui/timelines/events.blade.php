<div class="flow-root">
    @if($heading)
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">
                {{ $heading }}
            </h1>
            <p class="mt-2 text-sm text-gray-700">
                {{ $description }}
            </p>
        </div>
    </div>
    @endif
    <ul id="events-container" role="list" class="-mb-8"></ul>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const events = [];
        const eventsContainer = document.getElementById("events-container");

        function renderEvents() {
            eventsContainer.innerHTML = "";

            if (events.length === 0) {
                eventsContainer.innerHTML = `
                    <div class="text-sm text-gray-500 w-full text-center p-5">
                        No recent events.
                    </div>`;
                return;
            }

            events.forEach((event, index) => {
                if (!event) return;

                const li = document.createElement("li");

                li.innerHTML = `
                        <div class="relative pb-8">
                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex items-start space-x-3">
                                <div>
                                    <div class="relative px-1">
                                        <div class="flex size-8 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white">
                                            <svg class="size-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 py-1.5">
                                    <div class="text-sm text-gray-500">
                                        <a href="/stickle/User/${event?.data?.user?.id
                    }" class="font-medium text-gray-900">${event?.data?.model?.label || "User"
                    }</a>
                                        <span class="text-gray-400">
                                            ${event?.data?.type == "event"
                        ? "triggered"
                        : "visited "
                    }
                                        </span>
                                        ${event?.data?.properties?.name ||
                    event?.data?.properties?.path ||
                    event?.data?.properties?.title ||
                    event?.data?.properties?.url
                    }
                                        <span class="whitespace-nowrap">${window.stickleTimeAgo(event?.data?.timestamp)
                    }</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                eventsContainer.appendChild(li);
            });
        }

        // Initial render and setup updates
        renderEvents();

        // The ages are relative, so they go stale on their own. Redraw them on
        // a timer rather than only when a new event happens to arrive.
        setInterval(renderEvents, 30000);

        // Set up real-time updates, over the websocket where it is available
        // and by polling where it is not
        const stream = window.stickleStream({
            channel: "{{ $channel }}",
            endpoint: "{!! $requestsEndpoint !!}",
            intervalSeconds: {{ $pollInterval }},
            pollingEnabled: @json($pollingEnabled),
            onRecords: (records) => {
                // A polled record carries its own type where a broadcast
                // carries the event name, and the two are otherwise the
                // same shape.
                records.forEach((record) => {
                    events.unshift({ name: record.type, data: record });
                });

                if (events.length > 25) {
                    events.splice(25);
                }

                renderEvents();
            },
        });

        stream.start();
    });
</script>