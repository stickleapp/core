<x-stickle::ui.layouts.default-layout>
    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ $model->name }}
    </h1>

    <div class="w-full mb-4">
        <x-stickle::ui.partials.model-navigation :model="$model">
        </x-stickle::ui.partials.model-navigation>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div id="events" class="w-full md:w-3/5 pr-4 md:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.tables.models
                :heading="\Illuminate\Support\Str::of($relationship)->headline()"
                :subheading="sprintf('A full list of your %s.', \Illuminate\Support\Str::of($relationship)->plural())"
                :model-class="$modelClass"
            >
            </x-stickle::ui.tables.models>
        </div>

        <!-- 1/3 Column -->
        <div id="statistics" class="w-full md:w-2/5 pl-4 md:block hidden">
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.model-relationship
                :model="$model"
                :relationship="$relationship"
            >
            </x-stickle::ui.chartlists.model-relationship>
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
