<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs
            :pages="[
                [
                    'name' => \Illuminate\Support\Str::of($modelClass)->plural()->headline(),
                    'url' => route('stickle::models', ['modelClass' => $modelClass]),
                ],
                [
                    'name' => $model->name,
                    'url' => '#',
                ],
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <x-stickle::ui.partials.parent-model
        :model="$model"
    ></x-stickle::ui.partials.parent-model>

    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ $model->name }}
    </h1>

    <div class="w-full mb-4">
        <div class="mb-4">
            <x-stickle::ui.partials.model-navigation
                :model="$model"
                id="modelNavigation"
            >
            </x-stickle::ui.partials.model-navigation>
        </div>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div
            id="modelStatistics"
            class="modelNavigationContent w-full md:w-3/5 md:pr-4 md:block"
        >
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.model :model="$model">
            </x-stickle::ui.chartlists.model>
        </div>

        <div
            id="modelEvents"
            class="modelNavigationContent w-full md:w-2/5 md:pl-4 md:block"
        >
            <!-- Column 2 content here -->
            <h1 class="text-base font-semibold text-gray-900 pb-4">Events</h1>
            <x-stickle::ui.timelines.event-timeline
                :channel="sprintf(config('stickle.broadcasting.channels.object'),
                    str_replace('\\', '-', strtolower(class_basename($model))),
                    $model->getKey()
                )"
            ></x-stickle::ui.timelines.event-timeline>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>
