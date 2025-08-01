<div class="border-b border-gray-200">
    <div class="sm:flex sm:items-baseline">
        <h3 class="text-base font-semibold text-gray-900">
            {{ \Illuminate\Support\Str::of($modelClass)->plural()->headline() }}
        </h3>
        <div class="mt-4 sm:mt-0 sm:ml-10">
            <nav class="-mb-px flex space-x-8">
                <!-- Current: "border-indigo-500 text-indigo-600", Default: "" -->
                <a
                    href="{{ route('stickle::models', ['modelClass' => $modelClass]) }}"
                    @class([
                        'border-b-2 px-1 pb-4 text-sm font-medium whitespace-nowrap',
                        'border-indigo-500 text-indigo-600' => route('stickle::models', ['modelClass' => $modelClass]) == url()->current(),
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => route('stickle::models', ['modelClass' => $modelClass]) != url()->current(),
                    ])
                    aria-current="page"
                    >All</a
                ><a
                    href="{{ route('stickle::segments', ['modelClass' => $modelClass]) }}"
                    @class([
                        'border-b-2 px-1 pb-4 text-sm font-medium whitespace-nowrap',
                        'border-indigo-500 text-indigo-600' => route('stickle::segments', ['modelClass' => $modelClass]) == url()->current(),
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => route('stickle::segments', ['modelClass' => $modelClass]) != url()->current(),
                    ])
                    aria-current="page"
                    >Segments</a
                >
            </nav>
        </div>
    </div>
</div>
