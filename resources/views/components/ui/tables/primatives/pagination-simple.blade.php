<!-- Separate Pagination Component -->
<div
    x-data="pagination()"
    x-show="!$store.tableData.isLoading && !$store.tableData.error"
    class="mt-4 flex justify-between items-center flex-wrap gap-4"
>
    <div class="text-sm text-gray-600">
        Showing <span x-text="startIndex + 1"></span> to
        <span x-text="Math.min(endIndex, $store.tableData.totalRecords)"></span>
        of
        <span x-text="$store.tableData.totalRecords"></span>
        entries
    </div>

    <div class="flex space-x-1">
        <button
            @click="$store.tableData.setCurrentPage(1)"
            :disabled="$store.tableData.currentPage === 1 || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === 1 || $store.tableData.isLoading }"
            class="px-3 py-1 border rounded text-sm bg-white"
        >
            &laquo;
        </button>
        <button
            @click="$store.tableData.setCurrentPage($store.tableData.currentPage - 1)"
            :disabled="$store.tableData.currentPage === 1 || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === 1 || $store.tableData.isLoading }"
            class="px-3 py-1 border rounded text-sm bg-white"
        >
            &lsaquo;
        </button>

        <template x-for="page in pageNumbers" :key="page">
            <button
                @click="$store.tableData.setCurrentPage(page)"
                :disabled="$store.tableData.isLoading"
                :class="{ 'bg-blue-600 text-white': $store.tableData.currentPage === page, 'bg-white': $store.tableData.currentPage !== page }"
                class="px-3 py-1 border rounded text-sm"
            >
                <span x-text="page"></span>
            </button>
        </template>

        <button
            @click="$store.tableData.setCurrentPage($store.tableData.currentPage + 1)"
            :disabled="$store.tableData.currentPage === totalPages || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === totalPages || $store.tableData.isLoading }"
            class="px-3 py-1 border rounded text-sm bg-white"
        >
            &rsaquo;
        </button>
        <button
            @click="$store.tableData.setCurrentPage(totalPages)"
            :disabled="$store.tableData.currentPage === totalPages || $store.tableData.isLoading"
            :class="{ 'opacity-50 cursor-not-allowed': $store.tableData.currentPage === totalPages || $store.tableData.isLoading }"
            class="px-3 py-1 border rounded text-sm bg-white"
        >
            &raquo;
        </button>
    </div>

    <div class="flex items-center space-x-2">
        <label class="text-sm text-gray-600">Items per page:</label>
        <select
            x-model="$store.tableData.perPage"
            @change="$store.tableData.setPerPage($store.tableData.perPage)"
            :disabled="$store.tableData.isLoading"
            class="border rounded p-1 text-sm"
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
