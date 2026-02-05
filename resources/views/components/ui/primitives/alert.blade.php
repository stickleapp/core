@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
$types = [
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-200',
        'text' => 'text-blue-800',
        'icon' => 'text-blue-500',
    ],
    'success' => [
        'bg' => 'bg-green-50',
        'border' => 'border-green-200',
        'text' => 'text-green-800',
        'icon' => 'text-green-500',
    ],
    'warning' => [
        'bg' => 'bg-yellow-50',
        'border' => 'border-yellow-200',
        'text' => 'text-yellow-800',
        'icon' => 'text-yellow-500',
    ],
    'error' => [
        'bg' => 'bg-red-50',
        'border' => 'border-red-200',
        'text' => 'text-red-800',
        'icon' => 'text-red-500',
    ],
];

$icons = [
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />',
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
    'error' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
];

$config = $types[$type] ?? $types['info'];
$iconPath = $icons[$type] ?? $icons['info'];

$classes = implode(' ', [
    'relative w-full rounded-lg border p-4',
    $config['bg'],
    $config['border'],
    $config['text'],
]);
@endphp

<div
    {{ $attributes->merge(['class' => $classes]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $config['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                {!! $iconPath !!}
            </svg>
        </div>
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-medium">{{ $title }}</h3>
                <div class="mt-1 text-sm opacity-90">
                    {{ $slot }}
                </div>
            @else
                <p class="text-sm">{{ $slot }}</p>
            @endif
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button
                    type="button"
                    @click="show = false"
                    class="-mx-1.5 -my-1.5 inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $config['text'] }} hover:bg-white/50 focus:ring-{{ explode('-', $config['border'])[1] }}-500"
                >
                    <span class="sr-only">Dismiss</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
