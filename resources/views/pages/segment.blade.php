<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs
            :pages="[
                [
                    'name' => \Illuminate\Support\Str::of($modelClass)->plural()->headline(),
                    'url' => route('stickle::models', ['modelClass' => $modelClass]),
                ],
                [
                    'name' => 'Segments',
                    'url' => route('stickle::segments', ['modelClass' => $modelClass]),
                ],
                [
                    'name' => $segment->name,
                    'url' => '#',
                ],
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ $segment->name }}
    </h1>

    <div class="w-full mb-4 lg:hidden">
        <x-stickle::ui.partials.responsive-tabs
            :tabs="[
                [
                    'label' => 'List',
                    'target' => 'segmentList',
                ],
                [
                    'label' => 'Statistics & Events',
                    'target' => 'segmentSidebar',
                ],
            ]"
            :hide-tabs="true"
            id="segmentNavigation"
        >
        </x-stickle::ui.partials.responsive-tabs>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <div
            id="segmentList"
            class="segmentNavigationContent w-full lg:w-3/5 lg:pr-4 md:block"
        >
            <!-- Column 2 content here -->
            <x-stickle::ui.tables.segment
                :segment="$segment"
                :heading="$segment->name"
                :subheading="$segment->description"
            >
            </x-stickle::ui.tables.segment>
        </div>

        <div
            id="segmentSidebar"
            class="segmentNavigationContent w-full lg:w-2/5 lg:pl-4 hidden lg:block"
        >
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
