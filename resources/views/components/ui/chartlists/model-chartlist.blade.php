<div>
    @foreach ($chartData as $chart)
    <div class="mb-5">
        <p>{{ md5(json_encode($chart)) }}</p>
        <x-stickle-charts-model
            :key="md5(json_encode($chart))"
            :model="$model"
            :current-value="data_get($model, 'id')"
            :attribute="data_get($chart, 'attribute')"
            :chart-type="data_get($chart, 'chartType')"
            :label="data_get($chart, 'label')"
            :description="data_get($chart, 'description')"
            :data-type="data_get($chart, 'dataType')"
            :primary-aggregate="data_get($chart, 'primaryAggregate')"
        >
        </x-stickle-charts-model>
    </div>
    @endforeach
</div>
