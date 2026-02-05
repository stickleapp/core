@php $charts = $chartData(); @endphp
<div
    x-data="{
        currentPage: 0,
        totalItems: {{ count($charts) }},
        itemsPerPage: 4,
        touchStartX: 0,
        touchEndX: 0,
        get totalPages() { return Math.ceil(this.totalItems / this.itemsPerPage) },
        goTo(page) { this.currentPage = Math.max(0, Math.min(page, this.totalPages - 1)) },
        next() { this.goTo(this.currentPage + 1) },
        prev() { this.goTo(this.currentPage - 1) },
        updateItemsPerPage() {
            this.itemsPerPage = window.innerWidth >= 1024 ? 4 : window.innerWidth >= 640 ? 2 : 1;
            if (this.currentPage >= this.totalPages) this.currentPage = Math.max(0, this.totalPages - 1);
        },
        handleTouchStart(e) { this.touchStartX = e.changedTouches[0].screenX },
        handleTouchEnd(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            const diff = this.touchStartX - this.touchEndX;
            if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev() }
        },
    }"
    x-init="updateItemsPerPage(); window.addEventListener('resize', () => updateItemsPerPage())"
>
    <div
        class="overflow-hidden"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
    >
        <div
            class="flex transition-transform duration-300 ease-in-out"
            :style="'transform: translateX(-' + (currentPage * 100) + '%)'"
        >
            @foreach ($charts as $chart)
                @php
                    $attribute = data_get($chart, 'attribute');
                    $currentValue = '';
                @endphp
                <div class="w-full sm:w-1/2 lg:w-1/4 flex-shrink-0 sm:px-2">
                    <x-stickle::ui.charts.segment
                        :key="md5(json_encode($chart))"
                        :segment="$segment"
                        :attribute="data_get($chart, 'attribute')"
                        :chart-type="data_get($chart, 'chartType')"
                        :label="data_get($chart, 'label')"
                        :description="data_get($chart, 'description')"
                        :data-type="data_get($chart, 'dataType')"
                        :primary-aggregate="data_get($chart, 'primaryAggregate')"
                    >
                    </x-stickle::ui.charts.segment>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Dot navigation --}}
    <div x-show="totalPages > 1" class="flex justify-center mt-4 gap-2">
        <template x-for="page in totalPages" :key="page">
            <button
                @click="goTo(page - 1)"
                :class="currentPage === page - 1 ? 'bg-gray-800' : 'bg-gray-300'"
                class="w-2 h-2 rounded-full transition-colors duration-200 hover:bg-gray-500"
            ></button>
        </template>
    </div>
</div>
