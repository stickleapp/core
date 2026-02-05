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

    <div class="w-full mb-6">
        <x-stickle::ui.chartlists.segment :segment="$segment">
        </x-stickle::ui.chartlists.segment>
    </div>

    <div class="w-full">
        <x-stickle::ui.tables.segment
            :segment="$segment"
            :heading="$segment->name"
            :subheading="$segment->description"
        >
        </x-stickle::ui.tables.segment>
    </div>
</x-stickle::ui.layouts.default-layout>
