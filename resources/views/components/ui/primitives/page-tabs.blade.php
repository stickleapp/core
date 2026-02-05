@props([
    'tabs' => [],
])

<nav {{ $attributes->merge(['class' => '-mb-px flex space-x-8']) }}>
    @foreach($tabs as $tab)
        @php
            $isActive = $tab['active'] ?? false;
            $baseClasses = 'border-b-2 px-1 pb-4 text-sm font-medium whitespace-nowrap transition-colors';
            $activeClasses = $isActive
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700';
        @endphp
        <a
            href="{{ $tab['href'] ?? '#' }}"
            class="{{ $baseClasses }} {{ $activeClasses }}"
            @if($isActive) aria-current="page" @endif
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
