@props([
    'color' => 'gray',
    'size' => 'md',
    'rounded' => 'full',
])

@php
$colors = [
    'gray' => 'bg-gray-100 text-gray-800',
    'red' => 'bg-red-100 text-red-800',
    'green' => 'bg-green-100 text-green-800',
    'blue' => 'bg-blue-100 text-blue-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'indigo' => 'bg-indigo-100 text-indigo-800',
    'purple' => 'bg-purple-100 text-purple-800',
    'pink' => 'bg-pink-100 text-pink-800',
    'neutral' => 'bg-neutral-100 text-neutral-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-sm',
];

$roundedOptions = [
    'full' => 'rounded-full',
    'md' => 'rounded-md',
    'sm' => 'rounded-sm',
    'none' => 'rounded-none',
];

$classes = implode(' ', [
    'inline-flex items-center font-semibold',
    $colors[$color] ?? $colors['gray'],
    $sizes[$size] ?? $sizes['md'],
    $roundedOptions[$rounded] ?? $roundedOptions['full'],
]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
