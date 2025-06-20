<div
    x-show="delta"
    :class="{
            'bg-green-100 text-green-800': delta?.percentage_change > 0,
            'bg-red-100 text-red-800': delta?.percentage_change < 0,
            'bg-gray-100 text-gray-800': delta?.percentage_change == 0 || delta?.percentage_change == null
        }"
    class="inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium text-green-800 md:mt-2 lg:mt-0"
>
    <svg
        class="mr-0.5 -ml-1 size-5 shrink-0 self-center text-green-500"
        :class="{
            'bg-green-100 text-green-500': delta?.percentage_change > 0,
            'bg-red-100 text-red-500': delta?.percentage_change < 0,
            'bg-gray-100 text-gray-500': delta?.percentage_change == 0 || delta?.percentage_change == null
        }"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
        data-slot="icon"
    >
        <path
            fill-rule="evenodd"
            :d="delta?.percentage_change < 0 
        ? 'M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z'
        : 'M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z'"
            clip-rule="evenodd"
        />
    </svg>

    <span class="sr-only"> Increased by </span>
    <span x-text="delta?.percentage_change"></span>%
</div>
