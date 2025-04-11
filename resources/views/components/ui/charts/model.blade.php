<div
    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8 shadow-sm"
>
    <dt class="text-sm/6 font-medium text-gray-500">{{ $label }}</dt>
    <dd>
        <div
            class="inline-flex items-baseline rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800 md:mt-2 lg:mt-0"
        >
            <svg
                class="mr-0.5 -ml-1 size-5 shrink-0 self-center text-green-500"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
                data-slot="icon"
            >
                <path
                    fill-rule="evenodd"
                    d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z"
                    clip-rule="evenodd"
                />
            </svg>
            <span class="sr-only"> Increased by </span>
            2.02%
        </div>
    </dd>
    <dd
        class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900"
    >
        $405,091.00
    </dd>

    <x-stickle::ui.charts.primatives.line
        :endpoint="$endpoint()"
        :$key
    ></x-stickle::ui.charts.primatives.line>
</div>
