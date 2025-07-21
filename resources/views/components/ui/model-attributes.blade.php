<div x-data="modelData{{ md5($endpoint) }}()">
    <div class="model-attributes">
        <div class="loading" x-show="isLoading">Loading...</div>
        <div class="attributes" x-show="!isLoading">
            <template x-if="Object.keys(modelAttributes).length === 0">
                <div class="empty-state">No attributes available</div>
            </template>
            <dl class="grid grid-cols-1 sm:grid-cols-2">
                <template x-for="(value, key) in modelAttributes" :key="key">
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                        <dt class="text-sm/6 font-medium text-gray-900" x-text="toTitleCase(key)"></dt>
                        <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2" x-text="typeof value === 'object' ? JSON.stringify(value) : value"></dd>
                    </div>
                </template>
            </dl>
        </div>
    </div>
</div>

<script>
    function modelData{{ md5($endpoint) }}() {

        let data;

        const fetchModelData = async () => {
            this.isLoading = true;
            return await fetch("{!! $endpoint !!}")
                .then((response) => response.json())
                .then((data) => {
                    return data.data[0];
                })
                .catch((error) => {
                    console.error("Error fetching data:", error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        };

        return {
            isLoading: false,
            modelAttributes: {},
            async init() {
                const data = await fetchModelData();
                if (!data) return;
                this.setModelAttributes(data);
            },
            setModelAttributes(data) {

                this.modelAttributes = data || {};
            },
            toTitleCase(str) {
                return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
        }
    }
</script>
