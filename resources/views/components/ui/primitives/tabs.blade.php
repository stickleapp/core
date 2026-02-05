@props([
    'id' => null,
    'defaultTab' => 1,
])

@php
$tabId = $id ?? 'tabs-' . \Illuminate\Support\Str::random(8);
@endphp

<div
    x-data="{
        tabSelected: {{ $defaultTab }},
        tabId: '{{ $tabId }}',
        tabButtonClicked(tabButton) {
            this.tabSelected = parseInt(tabButton.getAttribute('data-tab'));
            this.tabRepositionMarker(tabButton);
        },
        tabRepositionMarker(tabButton) {
            this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
            this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
            this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
        },
        tabContentActive(tabIndex) {
            return this.tabSelected === tabIndex;
        }
    }"
    x-init="$nextTick(() => {
        const firstButton = $refs.tabButtons.querySelector('[data-tab=\'{{ $defaultTab }}\']');
        if (firstButton) tabRepositionMarker(firstButton);
    })"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    {{-- Tab Buttons --}}
    <div x-ref="tabButtons" class="relative inline-flex items-center justify-center w-full h-10 p-1 text-gray-500 bg-gray-100 rounded-lg select-none">
        {{ $tabs }}
        <div x-ref="tabMarker" class="absolute left-0 z-10 h-full duration-300 ease-out" x-cloak>
            <div class="w-full h-full bg-white rounded-md shadow-sm"></div>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="relative w-full mt-4 content">
        {{ $slot }}
    </div>
</div>
