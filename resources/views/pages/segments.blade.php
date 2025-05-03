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
                ]
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ sprintf('%s Segments', \Illuminate\Support\Str::of($modelClass)->headline()) }}
    </h1>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div id="events" class="w-full md:w-5/5 lg:pr-4 md:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.tables.segments
                :heading="sprintf('%s Segments', \Illuminate\Support\Str::of($modelClass)->headline())"
                :subheading="sprintf('A full list of your %s segments.', \Illuminate\Support\Str::of($modelClass)->headline())"
                :model-class="$modelClass"
            >
            </x-stickle::ui.tables.segments>
        </div>

        <!-- 1/3 Column -->
        <div
            id="statistics"
            class="w-full md:w-0/5 lg:pl-4 md:block hidden"
        ></div>
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
