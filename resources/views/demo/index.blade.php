

<!-- resources/views/demo.blade.php -->
 <x-blank-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cascade Demo') }}
        </h2>
    </x-slot>    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-4 h-screen">
                <!-- Top Row: 75% height, 3 iframes -->
                <div class="col-span-1 h-[66vh] sm:rounded-lg border-2">
                    <iframe src="/" class="w-full h-full"></iframe>
                </div>

                <div class="col-span-1 h-[66vh] sm:rounded-lg border-2">
                    <iframe src="{{ route('cascade::demo/admin') }}" class="w-full h-full"></iframe>
                </div>

                <div class="col-span-1 h-[66vh] sm:rounded-lg border-2">
                    <iframe src="http://localhost:1984" class="w-full h-full">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
