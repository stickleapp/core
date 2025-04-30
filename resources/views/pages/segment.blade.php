<x-stickle::ui.layouts.default-layout>
    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ $segment->name }}
    </h1>

    <div class="w-full mb-4 lg:hidden">
        <x-stickle::ui.partials.segment-navigation :segment="$segment">
        </x-stickle::ui.partials.segment-navigation>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div id="list" class="w-full lg:w-3/5 pr-4 md:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.tables.segment
                :segment="$segment"
                :heading="$segment->name"
                :subheading="$segment->description"
            >
            </x-stickle::ui.tables.segment>
        </div>

        <!-- 1/3 Column -->
        <div id="statistics" class="w-full lg:w-2/5 pl-4 hidden lg:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.segment :segment="$segment">
            </x-stickle::ui.chartlists.segment>
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
