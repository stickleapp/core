@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
])

@php
$baseClasses = 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold transition-colors';
$activeClasses = $active
    ? 'bg-gray-50 text-indigo-600'
    : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600';
$iconClasses = $active
    ? 'text-indigo-600'
    : 'text-gray-400 group-hover:text-indigo-600';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => $baseClasses . ' ' . $activeClasses]) }}
    @if($active) aria-current="page" @endif
>
    @if($icon)
        <span class="size-6 shrink-0 {{ $iconClasses }}">
            {{ $icon }}
        </span>
    @elseif(isset($iconSlot))
        <span class="size-6 shrink-0 {{ $iconClasses }}">
            {{ $iconSlot }}
        </span>
    @endif

    {{ $slot }}
</a>
