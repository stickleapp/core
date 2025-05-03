<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs
            :pages="[
                [
                    'name' => \Illuminate\Support\Str::of($modelClass)->plural()->headline(),
                    'url' => route('stickle::models', ['modelClass' => $modelClass]),
                ],
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <h1 class="scroll-m-20 text-3xl font-bold tracking-tight mb-5">
        {{ \Illuminate\Support\Str::plural(\Illuminate\Support\Str::title(str_replace('_', ' ', $modelClass))) }}
    </h1>

    <div class="w-full mb-4">
        <x-stickle::ui.partials.models-navigation :modelClass="$modelClass">
        </x-stickle::ui.partials.models-navigation>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div id="models" class="w-full md:w-3/5 lg:l-4 md:block">
            <div class="bg-white p-6 shadow-md">
                <x-stickle::ui.tables.models
                    :heading="\Illuminate\Support\Str::of($modelClass)->headline()"
                    :subheading="sprintf('A full list of your %s.', \Illuminate\Support\Str::of($modelClass)->plural())"
                    :model-class="$modelClass"
                >
                </x-stickle::ui.tables.models>
            </div>
        </div>

        <!-- 1/3 Column -->
        <div
            id="statistics"
            class="w-full md:w-2/5 lg:pl-4 lg:pb-4 hidden md:block"
        >
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.models :model-class="$modelClass">
            </x-stickle::ui.chartlists.models>
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
