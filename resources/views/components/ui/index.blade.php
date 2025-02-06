<x-stickle-ui-default-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Stickle UI') }}
        </h2>
    </x-slot>    

    
    <div class="py-9">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                    <x-stickle-segment-chart 
                    style="padding-bottom: -10px;"
                        type="line" 
                        title="Active Users" 
                        segment-id="13" 
                        attribute="count">
                    </x-stickle-segment-chart>
                </div>
            </div>
        </div>
    </div>
</x-stickle-ui-default-layout>
