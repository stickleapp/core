@props([
    'options' => [],
    'placeholder' => 'Select an option',
    'name' => null,
    'selected' => null,
])

@php
$selectId = 'select-' . \Illuminate\Support\Str::random(8);
@endphp

<div
    x-data="{
        selectOpen: false,
        selectedItem: {{ $selected ? json_encode($options[array_search($selected, array_column($options, 'value'))] ?? null) : 'null' }},
        selectableItems: {{ json_encode($options) }},
        selectableItemActive: null,
        selectId: '{{ $selectId }}',
        selectDropdownPosition: 'bottom',
        selectableItemIsActive(item) {
            return this.selectableItemActive && this.selectableItemActive.value === item.value;
        },
        selectableItemActiveNext() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index < this.selectableItems.length - 1) {
                this.selectableItemActive = this.selectableItems[index + 1];
                this.selectScrollToActiveItem();
            }
        },
        selectableItemActivePrevious() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index > 0) {
                this.selectableItemActive = this.selectableItems[index - 1];
                this.selectScrollToActiveItem();
            }
        },
        selectScrollToActiveItem() {
            if (this.selectableItemActive) {
                const activeElement = document.getElementById(this.selectableItemActive.value + '-' + this.selectId);
                if (activeElement && this.$refs.selectableItemsList) {
                    const newScrollPos = (activeElement.offsetTop + activeElement.offsetHeight) - this.$refs.selectableItemsList.offsetHeight;
                    this.$refs.selectableItemsList.scrollTop = newScrollPos > 0 ? newScrollPos : 0;
                }
            }
        },
        selectPositionUpdate() {
            if (!this.$refs.selectButton || !this.$refs.selectableItemsList) return;
            const selectDropdownBottomPos = this.$refs.selectButton.getBoundingClientRect().top +
                this.$refs.selectButton.offsetHeight +
                parseInt(window.getComputedStyle(this.$refs.selectableItemsList).maxHeight);
            this.selectDropdownPosition = window.innerHeight < selectDropdownBottomPos ? 'top' : 'bottom';
        }
    }"
    x-init="
        $watch('selectOpen', function() {
            if (!selectedItem) {
                selectableItemActive = selectableItems[0];
            } else {
                selectableItemActive = selectedItem;
            }
            setTimeout(function() {
                selectScrollToActiveItem();
            }, 10);
            selectPositionUpdate();
        });
    "
    @keydown.escape="if (selectOpen) { selectOpen = false; }"
    @keydown.down="if (selectOpen) { selectableItemActiveNext(); } else { selectOpen = true; } event.preventDefault();"
    @keydown.up="if (selectOpen) { selectableItemActivePrevious(); } else { selectOpen = true; } event.preventDefault();"
    @keydown.enter="selectedItem = selectableItemActive; selectOpen = false;"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    @if($name)
        <input type="hidden" name="{{ $name }}" :value="selectedItem ? selectedItem.value : ''" />
    @endif

    <button
        type="button"
        x-ref="selectButton"
        @click="selectOpen = !selectOpen"
        :class="{ 'ring-2 ring-neutral-400 ring-offset-2': selectOpen }"
        class="relative flex items-center justify-between w-full min-h-[38px] py-2 pl-3 pr-10 text-left bg-white border rounded-md shadow-sm cursor-default border-neutral-200/70 focus:outline-none text-sm"
    >
        <span x-text="selectedItem ? selectedItem.label : '{{ $placeholder }}'" class="truncate" :class="{ 'text-neutral-400': !selectedItem }"></span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-400">
                <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <ul
        x-show="selectOpen"
        x-ref="selectableItemsList"
        @click.away="selectOpen = false"
        x-transition:enter="transition ease-out duration-50"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100"
        :class="{ 'bottom-0 mb-10': selectDropdownPosition === 'top', 'top-0 mt-10': selectDropdownPosition === 'bottom' }"
        class="absolute w-full py-1 mt-1 overflow-auto text-sm bg-white rounded-md shadow-md max-h-56 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        x-cloak
    >
        <template x-for="item in selectableItems" :key="item.value">
            <li
                @click="selectedItem = item; selectOpen = false; $refs.selectButton.focus();"
                :id="item.value + '-' + selectId"
                :data-disabled="item.disabled"
                :class="{ 'bg-neutral-100 text-gray-900': selectableItemIsActive(item) }"
                @mousemove="selectableItemActive = item"
                class="relative flex items-center h-full py-2 pl-8 text-gray-700 cursor-default select-none data-[disabled]:opacity-50 data-[disabled]:pointer-events-none hover:bg-neutral-50"
            >
                <svg
                    x-show="selectedItem && selectedItem.value === item.value"
                    class="absolute left-0 w-4 h-4 ml-2 stroke-current text-neutral-600"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span class="block font-medium truncate" x-text="item.label"></span>
            </li>
        </template>
    </ul>
</div>
