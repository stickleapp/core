<!DOCTYPE html>
<html class="h-full bg-white" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config("app.name", "Laravel") }}</title>

    @if(file_exists(public_path('build/manifest.json')) && !in_array(app()->environment(), ['local', 'development']))
    <link rel="preload" as="style" href="{{ \StickleApp\Core\Facades\Asset::url('resources/css/app.css') }}" />
    <link rel="modulepreload" as="script" href="{{ \StickleApp\Core\Facades\Asset::url('resources/js/app.js') }}" />
    <link rel="stylesheet" href="{{ \StickleApp\Core\Facades\Asset::url('resources/css/app.css') }}" />
    <script type="module" src="{{ \StickleApp\Core\Facades\Asset::url('resources/js/app.js') }}"></script>
    @else
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <!-- Alpine.js -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.8/cdn.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Simple-DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/8.4/pusher.min.js"></script>

    <!-- Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.0.2/dist/echo.iife.min.js"></script>

    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: "reverb",
            key: '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost: '{{ config("broadcasting.connections.reverb.options.host") }}',
            wsPort: '{{ config("broadcasting.connections.reverb.options.port") }}',
            wssPort:
                '{{ config("broadcasting.connections.reverb.options.port") }}',
            forceTLS: true,
            enabledTransports: ["ws", "wss"],
        });
    </script>
    @stack('scripts')
</head>

<body class="h-full">
    {{-- Toast Notifications --}}
    <x-stickle::ui.primitives.toast position="top-right" />

    <div x-data="{ isOpen: false }">
        @include('stickle::components.ui.layouts.partials.menu', ['models => $models()'])

        <!-- Layout wrapper -->
        <div class="relative isolate flex min-h-svh w-full bg-white max-lg:flex-col lg:bg-zinc-100">

            <!-- Sidebar (desktop) -->
            @include('stickle::components.ui.layouts.partials.sidebar', ['models => $models()'])

            <!-- Mobile header -->
            @include('stickle::components.ui.layouts.partials.header')

            <!-- Main content -->
            <main class="flex flex-1 flex-col pb-2 lg:min-w-0 lg:pt-2 lg:pr-2 lg:pl-64">
                <div class="grow p-6 lg:rounded-lg lg:bg-white lg:p-10 lg:shadow-xs lg:ring-1 lg:ring-zinc-950/5">
                    <div class="mx-auto max-w-6xl">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
@stack('drawers')

</html>
