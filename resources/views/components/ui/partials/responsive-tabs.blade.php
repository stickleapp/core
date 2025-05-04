<div class="grid grid-cols-1 {{ $responsiveClass }}:hidden">
    <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
    <select
        id="{{ $id }}"
        aria-label="Select a tab"
        class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
    >
        @foreach($tabs as $tab)
        <option value="#{{ $tab['hash'] }}">{{ $tab["label"] }}</option>
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
<div class="hidden {{ $responsiveClass }}:block">
    <nav class="flex space-x-4" aria-label="Tabs">
        @foreach($tabs as $tab)
        <a
            href="#"
            data-target="#{{ $tab['hash'] }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-medium',
                'text-gray-500 hover:text-gray-700' => ! false,
                'bg-gray-100 text-gray-700' => true,
            ])
            {{ false ? 'aria-current="page"' : '' }}
            >{{ $tab['label'] }}</a
        >
        @endforeach
    </nav>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const select = document.querySelector(
            'select[id="{{ $id }}"]'
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
            document.querySelectorAll('.{{ $id }}').forEach(content => content.classList.add('hidden'));
            document.querySelector(targetTab.getAttribute('data-target')).classList.remove('hidden');
        }

        // Set initial active tab based on URL hash
        setActiveTab(window.location.hash || "#statistics");

        // Handle dropdown selection changes
        select.addEventListener("change", function () {
            const selectedHash = select.options[select.selectedIndex].value;
            window.location.hash = selectedHash;
            setActiveTab(selectedHash);
        });

        // Handle tab clicks
        tabs.forEach((tab) => {
            tab.addEventListener("click", function (e) {
                e.preventDefault();
                const hash = this.getAttribute("data-target");
                // history.pushState(null, null, hash);
                setActiveTab(hash);
            });
        });

        // Listen for hash changes (browser back/forward buttons)
        window.addEventListener("hashchange", function () {
            setActiveTab(window.location.hash);
        });
    });
</script>
