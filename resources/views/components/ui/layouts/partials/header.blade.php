<!-- Mobile header -->
<header class="flex items-center justify-between px-4 py-2 lg:hidden">
    <button
        type="button"
        @click="isOpen = true"
        class="-m-2.5 p-2.5 text-zinc-950"
    >
        <span class="sr-only">Open sidebar</span>
        <svg
            class="size-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
            />
        </svg>
    </button>

    <button
        type="button"
        class="-m-1.5 flex items-center p-1.5"
    >
        <span class="sr-only">Your profile</span>
        <span class="inline-flex size-8 items-center justify-center rounded-full bg-zinc-200">
            <svg class="size-5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </span>
    </button>
</header>
