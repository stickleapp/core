@props([
    'index' => 1,
])

<div
    x-show="tabContentActive({{ $index }})"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    {{ $attributes->merge(['class' => 'relative']) }}
    x-cloak
>
    {{ $slot }}
</div>
