<x-stickle-ui-default-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Stickle UI') }}
        </h2>
    </x-slot>    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div>
				<x-stickle-segment-chart 
					type="line" 
					title="Active Users" 
					segment="DailyActiveUsers" 
					attribute="count">
				</x-stickle-segment-chart>
            </div>
        </div>
    </div>
</x-stickle-ui-default-layout>
