@props([
    'name' => null,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
$maxWidthClasses = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    'full' => 'sm:max-w-full sm:mx-4',
];

$maxWidthClass = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['lg'];
$modalId = $name ?? 'modal-' . \Illuminate\Support\Str::random(8);
@endphp

<div
    x-data="{ modalOpen: false }"
    @if($name)
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') modalOpen = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') modalOpen = false"
    @endif
    @keydown.escape.window="{{ $closeable ? 'modalOpen = false' : '' }}"
    {{ $attributes->merge(['class' => 'relative z-50 w-auto h-auto']) }}
>
    {{-- Trigger --}}
    @if(isset($trigger))
        <div @click="modalOpen = true">
            {{ $trigger }}
        </div>
    @endif

    {{-- Modal --}}
    <template x-teleport="body">
        <div
            x-show="modalOpen"
            class="fixed inset-0 z-[99] flex items-center justify-center w-screen h-screen"
            x-cloak
        >
            {{-- Overlay --}}
            <div
                x-show="modalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @if($closeable) @click="modalOpen = false" @endif
                class="absolute inset-0 w-full h-full bg-black/40"
            ></div>

            {{-- Modal Container --}}
            <div
                x-show="modalOpen"
                x-trap.inert.noscroll="modalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full bg-white sm:rounded-lg {{ $maxWidthClass }}"
            >
                {{-- Header --}}
                @if(isset($header))
                    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-semibold text-neutral-900">
                            {{ $header }}
                        </h3>
                        @if($closeable)
                            <button
                                type="button"
                                @click="modalOpen = false"
                                class="flex items-center justify-center w-8 h-8 text-gray-600 rounded-full hover:text-gray-800 hover:bg-gray-50 focus:outline-none"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Body --}}
                <div class="px-6 py-4">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @if(isset($footer))
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-neutral-200 bg-neutral-50 sm:rounded-b-lg">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>
