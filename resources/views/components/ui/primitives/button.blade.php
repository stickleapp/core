@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium tracking-wide transition-colors duration-200 rounded-md focus:ring-2 focus:ring-offset-2 focus:outline-none';

$variants = [
    'primary' => 'text-white bg-neutral-950 hover:bg-neutral-900 focus:ring-neutral-900',
    'secondary' => 'text-neutral-500 bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100 focus:ring-neutral-100',
    'outline' => 'bg-white border-2 text-neutral-900 hover:text-white border-neutral-900 hover:bg-neutral-900 focus:ring-neutral-900',
    'ghost' => 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 focus:ring-neutral-100',
    'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'success' => 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-4 py-2 text-base',
    'xl' => 'px-6 py-3 text-base',
];

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';

$classes = implode(' ', [
    $baseClasses,
    $variants[$variant] ?? $variants['primary'],
    $sizes[$size] ?? $sizes['md'],
    $disabledClasses,
]);
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button', 'disabled' => $disabled]) }}>
        {{ $slot }}
    </button>
@endif
