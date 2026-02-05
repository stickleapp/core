@props([
    'label' => null,
])

<div {{ $attributes }}>
    @if($label)
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 mb-2">
            {{ $label }}
        </div>
    @endif

    <ul role="list" class="-mx-2 space-y-1">
        {{ $slot }}
    </ul>
</div>
