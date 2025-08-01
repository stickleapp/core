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
                                        <a href="/stickle/User/${
                                            event?.data?.user?.id
                                        }" class="font-medium text-gray-900">${
                    event?.data?.user?.name || "User"
                }</a>
                                        ${
                                            event?.data?.event ||
                                            event?.data?.path
                                        }
                                        <span class="whitespace-nowrap">${
                                            event?.created_at || "just now"
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

        // Update listener for Echo
        if (window.Echo) {
            window.Echo.channel("{{ $channel }}").listenToAll(
                (eventName, data) => {
                    events.unshift({ name: eventName, data: data.payload });
                    if (events.length > 25) {
                        events.pop();
                    }
                    console.log(eventName, data);
                    renderEvents();
                }
            );
        } else {
            console.error(
                "Please initialize a window.Echo object. https://laravel.com/docs/11.x/broadcasting#client-side-installation"
            );
        }
    });
</script>
