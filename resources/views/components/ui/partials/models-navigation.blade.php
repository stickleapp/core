<div class="mb-6 border-b border-gray-200">
    <div class="sm:flex sm:items-baseline">
        <h3 class="text-base font-semibold text-gray-900">
            {{ \Illuminate\Support\Str::of($modelClass)->plural()->headline() }}
        </h3>
        <div class="mt-4 sm:mt-0 sm:ml-10">
            <x-stickle::ui.primitives.page-tabs :tabs="[
                [
                    'label' => 'All',
                    'href' => route('stickle::models', ['modelClass' => $modelClass]),
                    'active' => route('stickle::models', ['modelClass' => $modelClass]) == url()->current(),
                ],
                [
                    'label' => 'Segments',
                    'href' => route('stickle::segments', ['modelClass' => $modelClass]),
                    'active' => route('stickle::segments', ['modelClass' => $modelClass]) == url()->current(),
                ],
            ]" />
        </div>
    </div>
</div>
