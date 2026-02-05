@props([
    'href' => null,
    'disabled' => false,
])

@php
$baseClasses = 'relative flex cursor-default select-none items-center rounded px-2 py-1.5 text-sm outline-none transition-colors';
$stateClasses = $disabled
    ? 'opacity-50 pointer-events-none'
    : 'hover:bg-neutral-100 cursor-pointer';
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $stateClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => $baseClasses . ' ' . $stateClasses . ' w-full text-left', 'disabled' => $disabled]) }}
    >
        {{ $slot }}
    </button>
@endif
