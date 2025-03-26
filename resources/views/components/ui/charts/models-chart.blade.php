<div class="rounded-lg bg-white shadow-sm">
    <div class="flex flex-col gap-y-1.5 p-6">
        <h3 class="tracking-tight text-sm font-normal">
            {{ $label }}
        </h3>
        <p class="text-sm text-muted-foreground">{{ $description }}</p>
    </div>
    <div class="p-6 pt-0 pb-4">
        <div class="h-[200px]">
            @switch ($chartType) @case(\StickleApp\Core\Enums\ChartType::LINE)
            <x-stickle-charts-primatives-line
                :endpoint="$endpoint()"
                :$key
            ></x-stickle-charts-primatives-line>
            @endswitch
        </div>
    </div>
</div>
