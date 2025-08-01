<div>
    @foreach ($chartData as $chart)
    <div class="mb-5">
        <x-stickle::ui.charts.models
            :key="md5(json_encode($chart))"
            :model-class="$modelClass"
            :attribute="data_get($chart, 'attribute')"
            :chart-type="data_get($chart, 'chartType')"
            :label="data_get($chart, 'label')"
            :description="data_get($chart, 'description')"
            :data-type="data_get($chart, 'dataType')"
            :primary-aggregate="data_get($chart, 'primaryAggregate')"
        >
        </x-stickle::ui.charts.models>
    </div>
    @endforeach
</div>
