<div class="grid grid-cols-1 md:hidden">
    <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
    <select
        aria-label="Select a tab"
        class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
    >
        <option>Statistics</option>
        <option selected>Events</option>
        @foreach($model->stickleRelationships() as $relationship)
        <option>{{ $relationship["label"] ?? \Illuminate\Support\Str::of($relationship["name"])->ucfirst()->headline() }}</option>
        @endforeach
    </select>
    <svg
        class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end fill-gray-500"
        viewBox="0 0 16 16"
        fill="currentColor"
        aria-hidden="true"
        data-slot="icon"
    >
        <path
            fill-rule="evenodd"
            d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
            clip-rule="evenodd"
        />
    </svg>
</div>
<div class="hidden md:block">
    <nav class="flex space-x-4" aria-label="Tabs">
        <!-- Current: "bg-gray-100 text-gray-700", Default: "text-gray-500 hover:text-gray-700" -->
        <a
            data-target="#statistics"
            class="md:hidden rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
            >Statistics</a
        >
        <a
            data-target="#events"
            class="md:hidden rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
            >Events</a
        >
        @foreach($model->stickleRelationships() as $relationship)
            @php
                $route = route('stickle::model.relationship', ['class' =>
            strtolower(class_basename($model)), 'uid' => $model->id, 'relatedClass'
            => $relationship['name'] ]);
                $current = ($route == url()->current()) ? true : false;
            @endphp            
        <a
            href="{{ $route }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium',
                'bg-gray-100 text-gray-700' => $current,
                'text-gray-500 hover:text-gray-700' => !$current,
            ])
            {{ $current ? 'aria-current="page"' : '' }}
            >{{ $relationship["label"] ??  \Illuminate\Support\Str::of($relationship["name"])->headline() }}
        </a>
        @endforeach
    </nav>
</div>
