<div class="rounded-m bg-card text-card-foreground shadow">
    <div
        class="gap-y-1.5 p-6 flex flex-row items-center justify-between space-y-0 pb-2"
    >
        <h3 class="">{{ $label }}</h3>
    </div>
    <div class="p-0">
        <x-stickle-charts-primatives-line
            :endpoint="$endpoint()"
            :$key
        ></x-stickle-charts-primatives-line>
    </div>
</div>
