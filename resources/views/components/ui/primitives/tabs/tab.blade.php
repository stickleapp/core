@props([
    'index' => 1,
])

<button
    type="button"
    data-tab="{{ $index }}"
    @click="tabButtonClicked($el)"
    {{ $attributes->merge(['class' => 'relative z-20 inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap']) }}
>
    {{ $slot }}
</button>
