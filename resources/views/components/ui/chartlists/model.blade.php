<div>
    @foreach ($chartData as $chart) @php $attribute = data_get($chart,
    'attribute'); $currentValue = data_get($model,
    'modelAttributes.data.'.$attribute) @endphp
    <div class="mb-5">
        <x-stickle::ui.charts.model
            :key="md5(json_encode($chart))"
            :model="$model"
            :current-value="$currentValue"
            :attribute="data_get($chart, 'attribute')"
            :chart-type="data_get($chart, 'chartType')"
            :label="data_get($chart, 'label')"
            :description="data_get($chart, 'description')"
            :data-type="data_get($chart, 'dataType')"
            :primary-aggregate="data_get($chart, 'primaryAggregate')"
        >
        </x-stickle::ui.charts.model>
    </div>
    @endforeach
</div>
