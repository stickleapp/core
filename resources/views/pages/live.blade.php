<x-stickle::ui.layouts.default-layout>
    <div class="mb-5">
        <x-stickle::ui.partials.breadcrumbs
            :pages="[
                [
                    'name' => 'Live',
                    'url' => route('stickle::live')
                ]
            ]"
        ></x-stickle::ui.partials.breadcrumbs>
    </div>

    <!-- Hero Section - Full Width Map -->
    <div
        class="w-full h-96 bg-blue-200 mb-6 rounded-lg flex items-center justify-center"
    >
        <x-stickle::ui.maps.live
            heading="Live User Activity Map"
            description="Real-time visualization of user locations and activity"
        />
    </div>

    <!-- Responsive Tabs Navigation -->
    <div class="w-full mb-4 block md:hidden">
        <x-stickle::ui.partials.responsive-tabs
            :tabs="[
                [
                    'label' => 'Users',
                    'target' => 'usersColumn',
                ],
                [
                    'label' => 'Details & Events',
                    'target' => 'detailsEventsColumn',
                ],
            ]"
            id="livePageNavigation"
        >
        </x-stickle::ui.partials.responsive-tabs>
    </div>

    <!-- Two Column Layout -->
    <div class="w-full flex flex-col md:flex-row gap-6">
        <!-- Left Column: Users List -->
        <div
            id="usersColumn"
            class="livePageNavigationContent w-full md:w-1/2 md:block"
        >
            <div class="p-6 rounded-lg h-96">
                <x-stickle::ui.timelines.sessions
                    :activities-endpoint="route('stickle::api.activities', ['model_class' => 'User'])"
                    :channel="$eventsChannel"
                    :location="$location"
                    :model-class="$modelClass"
                    :uid="$uid"
                >
                </x-stickle::ui.timelines.sessions>
            </div>
        </div>

        <!-- Right Column: Model Details & Events -->
        <div
            id="detailsEventsColumn"
            class="livePageNavigationContent w-full md:w-1/2 hidden md:block"
        >
            <!-- Model Details Section (top right) -->
            @if($model)
            <div class="p-6 rounded-lg mb-4 h-48 border border-gray-100">
                Model details component will go here (when a model is selected)
            </div>
            @endif

            <!-- Events List Section (bottom right) -->
            <div class="p-6 rounded-lg h-44">
                <x-stickle::ui.timelines.event-timeline
                    :channel="$eventsChannel"
                ></x-stickle::ui.timelines.event-timeline>
            </div>
        </div>
    </div>
</x-stickle::ui.layouts.default-layout>
