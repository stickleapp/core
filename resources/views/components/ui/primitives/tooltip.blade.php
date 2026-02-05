@props([
    'text' => '',
    'position' => 'top',
])

@php
$positions = [
    'top' => [
        'wrapper' => '-top-2 left-1/2 -translate-x-1/2 -translate-y-full',
        'arrow' => 'top-full left-1/2 -translate-x-1/2 border-t-neutral-900 border-l-transparent border-r-transparent border-b-transparent',
    ],
    'bottom' => [
        'wrapper' => '-bottom-2 left-1/2 -translate-x-1/2 translate-y-full',
        'arrow' => 'bottom-full left-1/2 -translate-x-1/2 border-b-neutral-900 border-l-transparent border-r-transparent border-t-transparent',
    ],
    'left' => [
        'wrapper' => 'top-1/2 -left-2 -translate-x-full -translate-y-1/2',
        'arrow' => 'left-full top-1/2 -translate-y-1/2 border-l-neutral-900 border-t-transparent border-b-transparent border-r-transparent',
    ],
    'right' => [
        'wrapper' => 'top-1/2 -right-2 translate-x-full -translate-y-1/2',
        'arrow' => 'right-full top-1/2 -translate-y-1/2 border-r-neutral-900 border-t-transparent border-b-transparent border-l-transparent',
    ],
];

$config = $positions[$position] ?? $positions['top'];
@endphp

<div
    x-data="{ tooltipVisible: false, tooltipText: '{{ $text }}' }"
    x-on:mouseenter="tooltipVisible = true"
    x-on:mouseleave="tooltipVisible = false"
    class="relative inline-flex"
    {{ $attributes }}
>
    {{ $slot }}

    <div
        x-show="tooltipVisible"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $config['wrapper'] }}"
        x-cloak
    >
        <div class="whitespace-nowrap rounded bg-neutral-900 px-2 py-1 text-xs font-medium text-white shadow-lg">
            <span x-text="tooltipText"></span>
        </div>
        <div class="absolute h-0 w-0 border-4 {{ $config['arrow'] }}"></div>
    </div>
</div>
