<!-- Full-screen menu for mobile -->
<div
    x-show="isOpen"
    x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 lg:hidden"
    role="dialog"
    aria-modal="true"
>
    <nav class="flex h-full min-h-0 flex-col bg-white">
        <!-- Brand section + close button -->
        <div class="flex items-center justify-between border-b border-zinc-950/5 p-4">
            <a href="/stickle" class="flex items-center gap-2">
                <img
                    class="h-8 w-auto"
                    src="https://tailwindui.com/plus-assets/img/logos/mark.svg?color=zinc&shade=900"
                    alt="Stickle"
                />
                <span class="text-sm font-semibold text-zinc-950">Stickle</span>
            </a>
            <button
                type="button"
                @click="isOpen = false"
                class="-m-2.5 p-2.5 text-zinc-500"
            >
                <span class="sr-only">Close menu</span>
                <svg
                    class="size-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Nav links (scrollable) -->
        <div class="flex flex-1 flex-col overflow-y-auto p-4">
            <div class="flex flex-col gap-0.5">
                <a
                    href="/stickle"
                    class="relative flex items-center gap-3 rounded-lg px-2 py-2 text-sm/5 font-medium text-zinc-950"
                >
                    <span class="absolute inset-y-1 left-0 w-0.5 rounded-full bg-zinc-950"></span>
                    <svg
                        class="size-5 shrink-0 text-zinc-950"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                        />
                    </svg>
                    Live
                </a>

                @foreach($models() as $className)
                <span class="relative flex flex-col">
                    <a
                        href="/stickle/{{ class_basename($className) }}"
                        class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm/5 font-medium text-zinc-600 hover:bg-zinc-950/5 hover:text-zinc-950"
                    >
                        <svg
                            class="size-5 shrink-0 text-zinc-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"
                            />
                        </svg>
                        {{ \Illuminate\Support\Str::of(strtolower(class_basename($className)))->headline()->plural() }}
                    </a>
                    <a
                        href="/stickle/{{ class_basename($className) }}/segments"
                        class="flex items-center gap-3 rounded-lg px-2 py-1.5 pl-10 text-sm/5 font-medium text-zinc-500 hover:bg-zinc-950/5 hover:text-zinc-950"
                    >
                        Segments
                    </a>
                </span>
                @endforeach
            </div>
        </div>

        <!-- Settings section -->
        <div class="flex flex-col border-t border-zinc-950/5 p-4">
            <a
                href="#"
                class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm/5 font-medium text-zinc-600 hover:bg-zinc-950/5 hover:text-zinc-950"
            >
                <svg
                    class="size-5 shrink-0 text-zinc-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                    />
                </svg>
                Settings
            </a>
        </div>
    </nav>
</div>
