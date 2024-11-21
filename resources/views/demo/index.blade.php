@php
    $bottomContent1 = 'This is the content for the first bottom section. It may contain a lot of text, and it will scroll vertically.';
    $bottomContent2 = 'Second bottom section content. It can be any HTML content, including lists, images, etc.';
    $bottomContent3 = 'Third bottom section content. Similar to the others, this will scroll if the content overflows.';
@endphp

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

                <!-- Bottom Row: 25% height, 3 scrollable divs -->
                <div class="col-span-1 h-[33vh] overflow-y-auto bg-gray-100 p-4">
                    <div class="max-h-full">
                        <!-- Content for the first bottom column -->
                        {{ $bottomContent1 }}
                    </div>
                </div>
                <div class="col-span-1 h-[33vh] overflow-y-auto bg-gray-100 p-4">
                    <div class="max-h-full">
                        <!-- Content for the second bottom column -->
                        {{ $bottomContent2 }}
                    </div>
                </div>
                <div class="col-span-1 h-[33vh] overflow-y-auto bg-gray-100 p-4">
                    <div class="max-h-full">
                        <!-- Content for the third bottom column -->
                        {{ $bottomContent3 }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
