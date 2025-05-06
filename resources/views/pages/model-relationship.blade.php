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
                    'url' => route('stickle::model', ['modelClass' => $modelClass, 'uid' => $model->getKey()]),
                ],
                [
                    'name' => \Illuminate\Support\Str::of($relationship)->headline(),
                    'url' => '#',
                ],
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <h1
        class="scroll-m-20 text-xl md:text-3xl md:font-bold tracking-tight pb-3 md:pb-6"
    >
        {{ $model->name }}:
        {{ \Illuminate\Support\Str::of($relationship)->headline() }}
    </h1>

    <p class="text-sm text-gray-500 mb-4">
        <a
            href="{{ route('stickle::model', [ 'modelClass' => $modelClass, 'uid' => $model->getKey()]) }}"
            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
        >
            <svg
                class="mr-1 w-4 h-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
                    clip-rule="evenodd"
                />
            </svg>
            Back to {{ $model->name }}
        </a>
    </p>

    <div class="w-full mb-4">
        <x-stickle::ui.partials.responsive-tabs
            :tabs="[
                [
                    'label' => 'List',
                    'target' => 'modelsList',
                ],
                [
                    'label' => 'Statistics',
                    'target' => 'modelRelationshipSidebar',
                ],
            ]"
            :hide-tabs="true"
            id="modelRelationshipNavigation"
        >
        </x-stickle::ui.partials.responsive-tabs>
    </div>

    <div class="w-full flex flex-col md:flex-row">
        <!-- 2/3 Column -->
        <div
            id="modelsList"
            class="modelRelationshipNavigationContent w-full lg:w-3/5 lg:pr-4 md:block"
        >
            <!-- Column 2 content here -->
            <x-stickle::ui.tables.model-relationship
                :heading="\Illuminate\Support\Str::of($relationship)->headline()"
                :subheading="sprintf('A full list of your %s.', \Illuminate\Support\Str::of($relationship)->plural())"
                :model="$model"
                :relationship="$relationship"
            >
            </x-stickle::ui.tables.model-relationship>
        </div>

        <!-- 1/3 Column -->
        <div
            id="modelRelationshipSidebar"
            class="modelRelationshipNavigationContent w-full lg:w-2/5 lg:pl-4 hidden lg:block"
        >
            <!-- Column 2 content here -->
            <x-stickle::ui.chartlists.model-relationship
                :model="$model"
                :relationship="$relationship"
            >
            </x-stickle::ui.chartlists.model-relationship>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>
