<x-stickle::ui.layouts.default-layout>
    <x-stickle::ui.partials.parent-model
        :model="$model"
    ></x-stickle::ui.partials.parent-model>
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
        <div id="list" class="w-full lg:w-3/5 pr-4 md:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.model :model="$model">
            </x-stickle::ui.chartlists.model>
        </div>

        <div id="statistics" class="w-full lg:w-2/5 pl-4 hidden lg:block">
            <!-- Column 2 content here -->
            <x-stickle::ui.timelines.event-timeline
                :channel="sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', strtolower(class_basename($model))),
                    $model->getKey()
                )"
            ></x-stickle::ui.timelines.event-timeline>
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
