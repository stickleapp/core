@props([
    'position' => 'top-right',
])

@php
$positions = [
    'top-left' => 'top-0 left-0',
    'top-center' => 'top-0 left-1/2 -translate-x-1/2',
    'top-right' => 'top-0 right-0',
    'bottom-left' => 'bottom-0 left-0',
    'bottom-center' => 'bottom-0 left-1/2 -translate-x-1/2',
    'bottom-right' => 'bottom-0 right-0',
];

$positionClass = $positions[$position] ?? $positions['top-right'];
@endphp

<div
    x-data="{
        toasts: [],
        toastsHovered: false,
        expanded: false,
        position: '{{ $position }}',
        addToast(toast) {
            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                type: toast.type || 'default',
                title: toast.title || '',
                message: toast.message || '',
                duration: toast.duration || 4000,
            });

            if (toast.duration !== 0) {
                setTimeout(() => this.removeToast(id), toast.duration || 4000);
            }
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @toast.window="addToast($event.detail)"
    class="fixed z-[100] p-4 {{ $positionClass }}"
    {{ $attributes }}
>
    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            :class="{
                'mb-3': index < toasts.length - 1
            }"
            class="relative flex w-80 rounded-lg border bg-white shadow-lg overflow-hidden"
        >
            {{-- Type indicator stripe --}}
            <div
                :class="{
                    'bg-neutral-500': toast.type === 'default',
                    'bg-green-500': toast.type === 'success',
                    'bg-blue-500': toast.type === 'info',
                    'bg-yellow-500': toast.type === 'warning',
                    'bg-red-500': toast.type === 'error'
                }"
                class="w-1 shrink-0"
            ></div>

            <div class="flex items-start gap-3 p-4 flex-1">
                {{-- Icon --}}
                <div class="shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'default'">
                        <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p x-show="toast.title" x-text="toast.title" class="text-sm font-medium text-neutral-900"></p>
                    <p x-show="toast.message" x-text="toast.message" class="text-sm text-neutral-500 mt-0.5"></p>
                </div>

                {{-- Close button --}}
                <button
                    @click="removeToast(toast.id)"
                    class="shrink-0 text-neutral-400 hover:text-neutral-600 focus:outline-none"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
