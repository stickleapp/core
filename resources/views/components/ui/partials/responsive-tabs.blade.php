<div class="grid grid-cols-1 {{ $responsiveClass }}:hidden">
    <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
    <select
        id="{{ $id }}Select"
        aria-label="Select a tab"
        class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
    >
        @foreach($tabs as $tab)
        <option value="{{ $tab['hash'] }}">{{ $tab["label"] }}</option>
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
@if(!$hideTabs)
{{ $hideTabs }}
<div class="hidden {{ $responsiveClass }}:block">
    <nav id="{{ $id }}Tabs" class="flex space-x-4" aria-label="Tabs">
        @foreach($tabs as $tab)
        <a
            href="#"
            data-target="{{ $tab['hash'] }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-70"
            >{{ $tab["label"] }}</a
        >
        @endforeach
    </nav>
</div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const select = document.querySelector('select[id="{{ $id }}Select"]');
        const tabs = document.querySelectorAll('nav[id="{{ $id}}Tabs"] a');
        const tabContents = document.querySelectorAll(".{{ $id }}Content");

        function setActiveState(hash) {
            let activeContent = document.getElementById(hash) ?? tabContents[0];

            // Indicate which tab is active
            tabs.forEach((tab) => {
                tab.setAttribute("aria-current", "");
                tab.classList.remove(
                    "bg-gray-100",
                    "text-gray-700",
                    "text-gray-500",
                    "hover:text-gray-700"
                );
                if (
                    tab.getAttribute("data-target") ===
                    activeContent.getAttribute("id")
                ) {
                    tab.setAttribute("aria-current", "page");
                    tab.classList.add("bg-gray-100", "text-gray-700");
                }
            });

            // Update select dropdown
            for (let i = 0; i < select.options.length; i++) {
                if (
                    select.options[i].value === activeContent.getAttribute("id")
                ) {
                    select.selectedIndex = i;
                    break;
                }
            }

            // Here you would show the corresponding content
            document
                .querySelectorAll(".{{ $id }}Content")
                .forEach((content) => content.classList.add("hidden"));
            activeContent.classList.remove("hidden");
        }

        // Set initial active tab based on URL hash
        setActiveState(window.location.hash?.substring(1));

        // Handle dropdown selection changes
        select.addEventListener("change", function () {
            const selectedHash = select.options[select.selectedIndex].value;
            history.pushState(null, null, "#" + selectedHash);
            setActiveState(selectedHash);
        });

        // Handle tab clicks
        tabs.forEach((tab) => {
            tab.addEventListener("click", function (e) {
                e.preventDefault();
                const hash = this.getAttribute("data-target");
                history.pushState(null, null, "#" + hash);
                setActiveState(hash);
            });
        });

        // Listen for hash changes (browser back/forward buttons)
        window.addEventListener("hashchange", function () {
            setActiveState(window.location.hash?.substring(1));
        });
    });
</script>
