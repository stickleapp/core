<!DOCTYPE html>
<html
    class="h-full bg-white"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config("app.name", "Laravel") }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script
            defer
            src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.8/cdn.js"
        ></script>

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
        <!--
  This example requires updating your template:

  ```
  <html class="h-full bg-white">
  <body class="h-full">
  ```
-->
        <div x-data="{ isOpen: false }">
            @include('stickle::components.ui.layouts.partials.menu', ['models =>
            $models()'])
            @include('stickle::components.ui.layouts.partials.sidebar', ['models
            => $models()'])

            <div class="lg:pl-72">
                <!-- HEADER -->
                @include('stickle::components.ui.layouts.partials.header')

                <main class="py-10">
                    <!-- CONTAINER -->
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <!-- Your content -->
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
    @stack('drawers')
</html>
