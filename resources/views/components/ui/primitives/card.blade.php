@props([
    'padding' => 'md',
    'shadow' => 'sm',
])

@php
$paddings = [
    'none' => '',
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
];

$shadows = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
];

$baseClasses = 'rounded-lg overflow-hidden border border-neutral-200/60 bg-white text-neutral-700';

$classes = implode(' ', [
    $baseClasses,
    $paddings[$padding] ?? $paddings['md'],
    $shadows[$shadow] ?? $shadows['sm'],
]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="border-b border-neutral-200/60 px-6 py-4">
            {{ $header }}
        </div>
    @endif

    <div @class([
        'p-6' => $padding === 'none' && !isset($header) && !isset($footer),
    ])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="border-t border-neutral-200/60 px-6 py-4 bg-neutral-50">
            {{ $footer }}
        </div>
    @endif
</div>
