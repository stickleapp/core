<!-- Main container with global store -->
<div x-data="tableApp()" x-init="init()">
    <!-- Search Bar -->
    <div class="mb-5">
        <div class="flex gap-4 items-center">
            <div class="flex-1">
                <div class="relative">
                    <div
                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                    >
                        <svg
                            class="w-4 h-4 text-gray-500"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 20 20"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                            />
                        </svg>
                    </div>
                    <input
                        type="search"
                        x-model="$store.tableData.searchTerm"
                        @keyup.enter="fetchData()"
                        class="block w-full p-2 pl-10 text-sm rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search..."
                    />
                </div>
            </div>
            <button
                @click="fetchData()"
                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none"
                :disabled="$store.tableData.isLoading"
            >
                Search
            </button>
        </div>
    </div>

    <!-- Data Table Component -->
    <div x-data="dataTable()">
        <div class="overflow-hidden">
            <!-- Table Header with Sorting -->
            <div class="flex justify-between items-center p-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">Sort by:</label>
                    <select
                        x-model="sortBy"
                        @change="updateSort()"
                        :disabled="$store.tableData.isLoading"
                        class="rounded p-1 text-sm"
                    >
                        <template x-for="column in columns">
                            <option
                                :value="column.key"
                                x-text="column.label"
                            ></option>
                        </template>
                    </select>
                    <button
                        @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'; updateSort()"
                        :disabled="$store.tableData.isLoading"
                        class="p-1 rounded"
                    >
                        <span
                            x-text="sortDirection === 'asc' ? '↑' : '↓'"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Table Content -->
            <div
                x-show="!$store.tableData.isLoading && !$store.tableData.error"
            >
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <template
                                x-for="column in columns"
                                :key="column.key"
                            >
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                    @click="sortBy = column.key; updateSort()"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span x-text="column.label"></span>
                                        <span
                                            x-show="sortBy === column.key"
                                            x-text="sortDirection === 'asc' ? '↑' : '↓'"
                                            class="text-gray-400"
                                        ></span>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template
                            x-for="(item, index) in $store.tableData.items"
                            :key="index"
                        >
                            <tr class="hover:bg-gray-50">
                                <template
                                    x-for="column in columns"
                                    :key="column.key"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        <a
                                            :href="modelUrl('{{
                                                $modelClass
                                            }}', item)"
                                            ><span
                                                x-text="item[column.key]"
                                            ></span
                                        ></a>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div
                    x-show="!$store.tableData.isLoading && $store.tableData.items.length === 0"
                    class="p-8 text-center text-gray-500"
                >
                    No data available
                </div>
            </div>
        </div>
    </div>

    <!-- Loading and Error States -->
    <div x-show="$store.tableData.isLoading" class="my-4 text-center py-6">
        <div
            class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"
        ></div>
        <p class="mt-2 text-gray-600">Loading data...</p>
    </div>

    <div
        x-show="$store.tableData.error"
        class="my-4 p-4 bg-red-100 text-red-700 rounded-md"
    >
        <p x-text="$store.tableData.error"></p>
    </div>

    <x-stickle::ui.tables.primatives.pagination-simple></x-stickle::ui.tables.primatives.pagination-simple>
</div>

<script>
    // Initialize Alpine Store for global state management
    document.addEventListener("alpine:init", () => {
        Alpine.store("tableData", {
            items: [],
            filteredItems: [],
            currentPage: 1,
            perPage: 25,
            totalRecords: 0,
            isLoading: false,
            error: null,
            searchTerm: "",
            filters: {},
            sortBy: "id",
            sortDirection: "asc",

            setCurrentPage(page) {
                this.currentPage = page;
                // Trigger data fetch from the main app component
                // We'll access this method through an event
                document.dispatchEvent(new CustomEvent("fetch-data"));
            },

            setPerPage(perPage) {
                this.perPage = perPage;
                this.currentPage = 1;
                document.dispatchEvent(new CustomEvent("fetch-data"));
            },

            setSorting(column, direction) {
                this.sortBy = column;
                this.sortDirection = direction;
                this.filters.sort_by = column;
                this.filters.sort_direction = direction;
                this.currentPage = 1;
                document.dispatchEvent(new CustomEvent("fetch-data"));
            },
        });
    });

    // Main application state
    function tableApp() {
        return {
            // API settings
            apiUrl: "{!! $endpoint() !!}", // Change this to your Laravel API endpoint

            init() {
                // Initial data fetch
                this.fetchData();

                // Listen for data fetch events from store methods
                document.addEventListener("fetch-data", () => {
                    this.fetchData();
                });
            },

            fetchData() {
                // Get the store data
                const store = Alpine.store("tableData");

                // Set loading state
                store.isLoading = true;
                store.error = null;

                // Build query parameters for the API request
                const params = new URLSearchParams({
                    model_class: "{{ $modelClass }}",
                });

                // Check if the API URL has query parameters
                if (this.apiUrl.includes("?")) {
                    // Split the URL into base URL and query string
                    const [baseUrl, queryString] = this.apiUrl.split("?");

                    // Parse the query string and add parameters to our params object
                    const existingParams = new URLSearchParams(queryString);
                    for (const [key, value] of existingParams.entries()) {
                        params.append(key, value);
                    }

                    // Update the apiUrl to be just the base URL
                    this.apiUrl = baseUrl;
                }

                params.append("page", store.currentPage);
                params.append("per_page", store.perPage);
                params.append("sort_by", store.sortBy);
                params.append("sort_direction", store.sortDirection);

                // Add search term if present
                if (store.searchTerm.trim() !== "") {
                    params.append("search", store.searchTerm.trim());
                }

                // Add any other filters
                Object.entries(store.filters).forEach(([key, value]) => {
                    if (value) {
                        params.append(key, value);
                    }
                });

                // Make the API request
                fetch(`${this.apiUrl}?${params.toString()}`)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        // Laravel pagination format typically includes:
                        // data, current_page, last_page, per_page, total, etc.

                        store.items = data.data; // The actual records
                        store.filteredItems = data.data;
                        store.totalRecords = data.total;
                        store.currentPage = data.current_page;

                        // Calculate total pages and update perPage if needed
                        if (data.per_page && data.per_page !== store.perPage) {
                            store.perPage = data.per_page;
                        }
                    })
                    .catch((error) => {
                        console.error("Error fetching data:", error);
                        store.error =
                            "Failed to load data. Please try again later.";
                    })
                    .finally(() => {
                        store.isLoading = false;
                    });
            },
        };
    }

    // Data Table Component
    function dataTable() {
        return {
            columns: [
                { key: "id", label: "ID" },
                { key: "name", label: "Name" },
                { key: "email", label: "Email" },
                { key: "status", label: "Status" },
                { key: "created_at", label: "Created Date" },
            ],
            sortBy: "id",
            sortDirection: "asc",

            init() {
                // Initialize sort values in the store
                Alpine.store("tableData").sortBy = this.sortBy;
                Alpine.store("tableData").sortDirection = this.sortDirection;
            },

            // Update sort and trigger API fetch with new sort parameters
            updateSort() {
                Alpine.store("tableData").setSorting(
                    this.sortBy,
                    this.sortDirection
                );
            },

            modelUrl(modelClass, model) {
                // TODO: uid / not id?
                return modelClass + "/" + model.id;
            },
        };
    }
</script>
