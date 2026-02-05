@props([
    'align' => 'left',
    'width' => '48',
])

@php
$alignmentClasses = [
    'left' => 'left-0 origin-top-left',
    'right' => 'right-0 origin-top-right',
    'center' => 'left-1/2 -translate-x-1/2 origin-top',
];

$widthClasses = [
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
    'full' => 'w-full',
];

$alignClass = $alignmentClasses[$align] ?? $alignmentClasses['left'];
$widthClass = $widthClasses[$width] ?? $widthClasses['48'];
@endphp

<div
    x-data="{ dropdownOpen: false }"
    @keydown.escape.window="dropdownOpen = false"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    {{-- Trigger --}}
    <div @click="dropdownOpen = !dropdownOpen">
        {{ $trigger }}
    </div>

    {{-- Dropdown Content --}}
    <div
        x-show="dropdownOpen"
        @click.away="dropdownOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute z-50 mt-2 {{ $alignClass }} {{ $widthClass }}"
        x-cloak
    >
        <div class="p-1 bg-white rounded-md border shadow-md border-neutral-200/70 text-neutral-700">
            {{ $slot }}
        </div>
    </div>
</div>
