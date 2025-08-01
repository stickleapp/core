<div
    x-data="pagination()"
    x-show="!$store.tableData.isLoading && !$store.tableData.error"
    class="border-t border-gray-200 bg-white py-3 px-0 sm:px-6"
>
    <!-- Mobile view pagination -->
    <div class="flex flex-1 justify-between md:hidden">
        <button
            @click="$store.tableData.setCurrentPage($store.tableData.currentPage - 1)"
            :disabled="$store.tableData.currentPage === 1 || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === 1 || $store.tableData.isLoading }"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Previous
        </button>
        <button
            @click="$store.tableData.setCurrentPage($store.tableData.currentPage + 1)"
            :disabled="$store.tableData.currentPage === totalPages || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === totalPages || $store.tableData.isLoading }"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Next
        </button>
    </div>

    <!-- Desktop view pagination -->
    <div
        class="hidden md:flex md:flex-1 md:items-center md:justify-between mb-5"
    >
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium" x-text="startIndex + 1"></span>
                to
                <span
                    class="font-medium"
                    x-text="Math.min(endIndex, $store.tableData.totalRecords)"
                ></span>
                of
                <span
                    class="font-medium"
                    x-text="$store.tableData.totalRecords"
                ></span>
                results
            </p>
        </div>

        <div class="flex items-center">
            <nav
                class="isolate inline-flex -space-x-px rounded-md shadow-xs"
                aria-label="Pagination"
            >
                <!-- First page button -->
                <button
                    @click="$store.tableData.setCurrentPage(1)"
                    :disabled="$store.tableData.currentPage === 1 || $store.tableData.isLoading"
                    :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === 1 || $store.tableData.isLoading }"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                >
                    <span class="sr-only">First</span>
                    &laquo;
                </button>

                <!-- Previous page button -->
                <button
                    @click="$store.tableData.setCurrentPage($store.tableData.currentPage - 1)"
                    :disabled="$store.tableData.currentPage === 1 || $store.tableData.isLoading"
                    :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === 1 || $store.tableData.isLoading }"
                    class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                >
                    <span class="sr-only">Previous</span>
                    <svg
                        class="size-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>

                <!-- Page numbers -->
                <template x-for="page in pageNumbers" :key="page">
                    <button
                        @click="$store.tableData.setCurrentPage(page)"
                        :disabled="$store.tableData.isLoading"
                        :class="$store.tableData.currentPage === page 
                            ? 'relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                            : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0'"
                        aria-current="$store.tableData.currentPage === page ? 'page' : undefined"
                    >
                        <span x-text="page"></span>
                    </button>
                </template>

                <!-- Next page button -->
                <button
                    @click="$store.tableData.setCurrentPage($store.tableData.currentPage + 1)"
                    :disabled="$store.tableData.currentPage === totalPages || $store.tableData.isLoading"
                    :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === totalPages || $store.tableData.isLoading }"
                    class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                >
                    <span class="sr-only">Next</span>
                    <svg
                        class="size-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>

                <!-- Last page button -->
                <button
                    @click="$store.tableData.setCurrentPage(totalPages)"
                    :disabled="$store.tableData.currentPage === totalPages || $store.tableData.isLoading"
                    :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === totalPages || $store.tableData.isLoading }"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                >
                    <span class="sr-only">Last</span>
                    &raquo;
                </button>
            </nav>
        </div>
    </div>
    <div class="flex flex-1 w-full">
        <label class="text-sm text-gray-600">Items per page:</label>
        <select
            x-model="$store.tableData.perPage"
            @change="$store.tableData.setPerPage($store.tableData.perPage)"
            :disabled="$store.tableData.isLoading"
            class="rounded p-1 text-sm"
        >
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>
</div>

<script>
    // Pagination Component
    function pagination() {
        return {
            // Using Alpine's store to access the shared state
            get startIndex() {
                return (
                    (Alpine.store("tableData").currentPage - 1) *
                    Alpine.store("tableData").perPage
                );
            },

            get endIndex() {
                return (
                    this.startIndex +
                    parseInt(Alpine.store("tableData").perPage)
                );
            },

            get totalPages() {
                return Math.ceil(
                    Alpine.store("tableData").totalRecords /
                        Alpine.store("tableData").perPage
                );
            },

            get pageNumbers() {
                const currentPage = Alpine.store("tableData").currentPage;
                const totalPages = this.totalPages;

                // Show up to 5 page numbers with current page in the center if possible
                if (totalPages <= 5) {
                    return Array.from({ length: totalPages }, (_, i) => i + 1);
                }

                // Calculate range to show
                let startPage = Math.max(1, currentPage - 2);
                let endPage = startPage + 4;

                if (endPage > totalPages) {
                    endPage = totalPages;
                    startPage = Math.max(1, endPage - 4);
                }

                return Array.from(
                    { length: endPage - startPage + 1 },
                    (_, i) => startPage + i
                );
            },
        };
    }
</script>
