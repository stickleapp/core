<div class="rounded-xl bg-card text-card-foreground shadow-sm">
    <div
        class="gap-y-1.5 p-6 flex flex-row items-center justify-between space-y-0 pb-2"
    >
        <h3 class="tracking-tight text-sm font-normal">{{ $label }}</h3>
    </div>
    <div class="p-6 pt-0">
        <x-stickle-charts-primatives-info
            :endpoint="$endpoint()"
            :$key
        ></x-stickle-charts-primatives-info>
    </div>
</div>
