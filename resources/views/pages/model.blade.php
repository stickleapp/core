<x-stickle-ui-default-layout>
    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-4 md:pb-8"
    >
        {{ $model->name }}
    </h1>

    <!-- Navigation for medium screens -->
    <div class="w-full flex justify-left space-x-4 p-4 md:hidden">
        <button class="tab-button" data-target="#statistics">Statistics</button>
        <button class="tab-button" data-target="#events">Events</button>
        <button class="tab-button" data-target="#users">Users</button>
        <button class="tab-button" data-target="#children">Children</button>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div id="events" class="w-full md:w-3/5 pr-4 md:block">
            <div class="bg-white p-6 shadow-sm">
                <!-- Column 2 content here -->
                <x-stickle-events-timeline
                    :channel="config('stickle.broadcasting.channels.firehose')"
                ></x-stickle-events-timeline>
            </div>
        </div>

        <!-- 1/3 Column -->
        <div id="statistics" class="w-full md:w-2/5 pb-4 md:block hidden">
            <!-- Column 2 content here -->
            <x-stickle-chartlists-model :model="$object">
            </x-stickle-chartlists-model>
        </div>
    </div>
</x-stickle-ui-default-layout>

<script>
    document.querySelectorAll(".tab-button").forEach((button) => {
        button.addEventListener("click", () => {
            document
                .querySelectorAll(".tab-button")
                .forEach((btn) => btn.classList.remove("active"));
            button.classList.add("active");

            document
                .querySelectorAll(".md\\:block")
                .forEach((column) => column.classList.add("hidden"));
            document
                .querySelector(button.getAttribute("data-target"))
                .classList.remove("hidden");
        });
    });
</script>
