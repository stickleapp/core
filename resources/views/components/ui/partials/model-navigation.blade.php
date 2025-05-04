<div class="grid grid-cols-1 md:hidden">
    <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
    <select
        id="modelNavigationSelect"
        aria-label="Select a tab"
        class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
    >
    
        <option value="#statistics">Statistics</option>
        <option value="#events" selected>Events</option>
        @foreach($model->stickleRelationships([\Illuminate\Database\Eloquent\Relations\HasMany::class]) as $relationship)
            @php
                $route = route('stickle::model.relationship', ['modelClass' => class_basename($model), 'uid' => $model->id, 'relationship' => $relationship->name ]);
                $current = ($route == url()->current()) ? true : false;
            @endphp
        <option value="{{ $route }}">{{ $relationship->label ?? \Illuminate\Support\Str::of($relationship->name)->ucfirst()->headline() }}</option>
        @endforeach
    </select>
    <svg
        class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end fill-gray-500"
        viewBox="0 0 16 16"
        fill="currentColor"
        aria-hidden="true"
        data-slot="icon"
    >
        <path
            fill-rule="evenodd"
            d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
            clip-rule="evenodd"
        />
    </svg>
</div>

<div class="hidden md:block">
    <nav class="flex space-x-4" aria-label="Tabs">
        <!-- Current: "bg-gray-100 text-gray-700", Default: "text-gray-500 hover:text-gray-700" -->
        @foreach($model->stickleRelationships([\Illuminate\Database\Eloquent\Relations\HasMany::class]) as $relationship)
            @php
                $route = route('stickle::model.relationship', ['modelClass' => class_basename($model), 'uid' => $model->id, 'relationship' => $relationship->name ]);
                $current = ($route == url()->current()) ? true : false;
            @endphp
        <a
            href="{{ $route }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium',
                'text-gray-500 hover:text-gray-700' => ! $current,
                'bg-gray-100 text-gray-700' => $current,
            ])
            {{ $current ? 'aria-current="page"' : '' }}
            >{{ $relationship->label ??  \Illuminate\Support\Str::of($relationship->name)->headline() }}
        </a>
        @endforeach
    </nav>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const select = document.querySelector(
            'select[id="modelNavigationSelect"]'
        );
        const tabs = document.querySelectorAll('nav[aria-label="Tabs"] a');

        // Function to set the active tab based on URL hash
        function setActiveTab(hash) {
            // Default to first tab if no hash or matching tab found
            let targetTab = tabs[0];

            if (hash) {
                // Find tab with matching data-target
                for (const tab of tabs) {
                    if (tab.getAttribute("data-target") === hash) {
                        targetTab = tab;
                        break;
                    }
                }
            }

            // Reset all tabs
            tabs.forEach((t) => {
                t.setAttribute("aria-current", "");
                t.classList.remove("bg-gray-100", "text-gray-700");
                t.classList.add("text-gray-500", "hover:text-gray-700");
            });

            // Set active tab
            targetTab.setAttribute("aria-current", "page");
            targetTab.classList.remove("text-gray-500", "hover:text-gray-700");
            targetTab.classList.add("bg-gray-100", "text-gray-700");

            // Update select dropdown
            for (let i = 0; i < select.options.length; i++) {
                if (
                    select.options[i].value ===
                    targetTab.getAttribute("data-target")
                ) {
                    select.selectedIndex = i;
                    break;
                }
            }

            // Here you would show the corresponding content
            // document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            // document.querySelector(targetTab.getAttribute('data-target')).classList.remove('hidden');
        }

        // Set initial active tab based on URL hash
        // setActiveTab(window.location.hash || "#models");

        // Handle dropdown selection changes
        select.addEventListener("change", function () {
            const selectedValue = select.options[select.selectedIndex].value;
            const isHash = selectedValue.startsWith("#");
            if (isHash) {
                // If the selected value is a hash, set it as the hash in the URL
                window.location.hash = selectedValue;
                setActiveTab(selectedValue);
            } else {
                // Otherwise, navigate to the full URL
                window.location.href = selectedValue;
            }
        });

        // Handle tab clicks
        // tabs.forEach((tab) => {
        //     tab.addEventListener("click", function (e) {
        //         e.preventDefault();
        //         const hash = this.getAttribute("data-target");
        //         window.location.hash = hash;
        //         setActiveTab(hash);
        //     });
        // });

        // Listen for hash changes (browser back/forward buttons)
        window.addEventListener("hashchange", function () {
            setActiveTab(window.location.hash);
        });
    });
</script>