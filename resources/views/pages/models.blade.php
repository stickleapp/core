<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs :pages="[
                [
                    'name' => \Illuminate\Support\Str::of($modelClass)->plural()->headline(),
                    'url' => route('stickle::models', ['modelClass' => $modelClass]),
                ],
            ]"></x-stickle::ui.partials.breadcrumbs>
    </div>

    <x-stickle::ui.partials.models-navigation :model-class="$modelClass" />

    <div class="w-full mb-4">
        <x-stickle::ui.partials.responsive-tabs :tabs="[
                [
                    'label' => 'List',
                    'target' => 'modelsList',
                ],
                [
                    'label' => 'Statistics & Events',
                    'target' => 'modelsSidebar',
                ],
            ]" :hide-tabs="true" id="modelsNavigation">
        </x-stickle::ui.partials.responsive-tabs>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <div id="modelsList" class="modelsNavigationContent w-full md:w-3/5 md:l-4 md:block">
            <x-stickle::ui.tables.models :heading="\Illuminate\Support\Str::of($modelClass)->headline()"
                    :subheading="sprintf('A full list of your %s.', \Illuminate\Support\Str::of($modelClass)->plural())"
                    :model-class="$modelClass">
                </x-stickle::ui.tables.models>
        </div>

        <div id="modelsSidebar" class="modelsNavigationContent w-full md:w-2/5 md:pl-4 md:pb-4 hidden md:block">
            <div class="w-full mb-4">
                <div class="mb-4">
                    <x-stickle::ui.partials.responsive-tabs :tabs="[
                            [
                                'label' => 'Statistics',
                                'target' => 'modelsStatistics',
                            ],
                            [
                                'label' => 'Events',
                                'target' => 'modelsEvents',
                            ],
                        ]" id="modelsSideBarToggle">
                    </x-stickle::ui.partials.responsive-tabs>
                </div>

                <!-- Column 2 content here -->
                <div id="modelsStatistics" class="modelsSideBarToggleContent w-full hidden">
                    <x-stickle::ui.chartlists.models :model-class="$modelClass">
                    </x-stickle::ui.chartlists.models>
                </div>

                <div id="modelsEvents" class="modelsSideBarToggleContent w-full hidden">
                    <x-stickle::ui.timelines.events :channel="sprintf(config('stickle.broadcasting.channels.class'),
                        str_replace('\\', '-', strtolower(class_basename($modelClass)))
                    )"></x-stickle::ui.timelines.events>
                </div>
            </div>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>