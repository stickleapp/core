<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs :pages="[
                [
                    'name' => \Illuminate\Support\Str::of($modelClass)->plural()->headline(),
                    'url' => route('stickle::models', ['modelClass' => $modelClass]),
                ],
                [
                    'name' => $model->stickleLabel(),
                    'url' => '#',
                ],
            ]"></x-stickle::ui.partials.breadcrumbs>
    </div>

    <x-stickle::ui.partials.parent-model :model="$model"></x-stickle::ui.partials.parent-model>

    <h1 class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6">
        {{ $model->stickleLabel() }}
    </h1>

    {{-- Desktop: Statistics Carousel (hidden on mobile) --}}
    <div class="hidden lg:block mb-6">
        <x-stickle::ui.chartlists.model-carousel :model="$model">
        </x-stickle::ui.chartlists.model-carousel>
    </div>

    {{-- Tabs Section --}}
    <div class="w-full">
        <div class="mb-4">
            <x-stickle::ui.partials.responsive-tabs :tabs="[
                    [
                        'label' => 'Statistics',
                        'target' => 'modelStatistics',
                        'hideOnDesktop' => true,
                    ],
                    [
                        'label' => 'Details',
                        'target' => 'modelDetails',
                    ],
                    [
                        'label' => 'Events',
                        'target' => 'modelEvents',
                    ],
                ]" id="modelTabs" responsiveClass="lg">
            </x-stickle::ui.partials.responsive-tabs>
        </div>

        {{-- Statistics Tab Content (mobile only, hidden on desktop) --}}
        <div id="modelStatistics" class="modelTabsContent w-full hidden lg:hidden">
            <x-stickle::ui.chartlists.model :model="$model">
            </x-stickle::ui.chartlists.model>
        </div>

        {{-- Details Tab Content --}}
        <div id="modelDetails" class="modelTabsContent w-full hidden">
            <x-stickle::ui.model-attributes :model="$model"
                :heading="\Illuminate\Support\Str::of($model->stickleLabel())->headline()" :subheading="sprintf('A full list of your %s attributes.',
            \Illuminate\Support\Str::of($modelClass)->plural())">
            </x-stickle::ui.model-attributes>
        </div>

        {{-- Events Tab Content --}}
        <div id="modelEvents" class="modelTabsContent w-full hidden">
            <x-stickle::ui.timelines.events :channel="sprintf(config('stickle.broadcasting.channels.object'),
            str_replace('\\', '-', strtolower(class_basename($model))),
            $model->getKey()
        )"></x-stickle::ui.timelines.events>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>
