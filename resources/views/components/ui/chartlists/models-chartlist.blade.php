<div>
    @foreach ($chartData as $chart)
    <div class="mb-5">
        <x-stickle-charts-models
            :model="$model"
            :attribute="data_get($chart, 'attribute')"
        >
            @switch (data_get($chart, 'chartType')) @case('line')
            <x-stickle-charts-primatives-line></x-stickle-charts-primatives-line>
            @case('bar')
            <x-stickle-charts-primatives-line></x-stickle-charts-primatives-line>
            @case('pie')
            <x-stickle-charts-primatives-line></x-stickle-charts-primatives-line>
            @case ('info')
            <x-stickle-charts-primatives-line></x-stickle-charts-primatives-line>
            @endswitch
        </x-stickle-charts-models>
    </div>
    @endforeach
</div>
