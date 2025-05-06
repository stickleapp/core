<div class="sticky top-0 z-40 lg:mx-auto lg:max-w-7xl lg:px-8">
    <div
        class="flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-xs sm:gap-x-6 sm:px-6 lg:px-0 lg:shadow-none"
    >
        <button
            type="button"
            @click="isOpen = true"
            class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
        >
            <span class="sr-only">Open sidebar</span>
            <svg
                class="size-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                aria-hidden="true"
                data-slot="icon"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
            </svg>
        </button>

        <!-- Separator -->
        <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <form class="grid flex-1 grid-cols-1" action="#" method="GET">
                <input
                    type="search"
                    name="search"
                    aria-label="Search"
                    class="col-start-1 row-start-1 block size-full bg-white pl-8 text-base text-gray-900 outline-hidden placeholder:text-gray-400 sm:text-sm/6"
                    placeholder="Search"
                />
                <svg
                    class="pointer-events-none col-start-1 row-start-1 size-5 self-center text-gray-400"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                    data-slot="icon"
                >
                    <path
                        fill-rule="evenodd"
                        d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </form>
        </div>
    </div>
</div>
