<x-stickle::ui.layouts.default-layout>
    <div class="w-full p-4">
        <!-- Full-width row -->
        <div class="bg-white shadow-md">
            <x-stickle::ui.charts.segment
                type="line"
                title="Active Users"
                segment-id="7"
                attribute="count"
            >
            </x-stickle::ui.charts.segment>
        </div>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- Navigation for medium screens -->
        <div class="md:hidden flex justify-left space-x-4 p-4">
            <button class="tab-button" data-target="#column1">Users</button>
            <button class="tab-button" data-target="#column2">Events</button>
        </div>

        <!-- 2/3 Column -->
        <div id="column1" class="w-full md:w-2/3 p-4 md:block">
            <div class="bg-white p-6 shadow-md">
                <x-stickle::ui.tables.segment
                    title="Active Users"
                    segment-id="7"
                >
                </x-stickle::ui.tables.segment>
            </div>
        </div>

        <!-- 1/3 Column -->
        <div id="column2" class="w-full md:w-1/3 p-4 md:block hidden">
            <div class="bg-white p-6 shadow-md">
                <!-- Column 2 content here -->
                <x-stickle::timelines.events
                    :channel="config('stickle.broadcasting.channels.firehose')"
                ></x-stickle::timelines.events>
            </div>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>

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
