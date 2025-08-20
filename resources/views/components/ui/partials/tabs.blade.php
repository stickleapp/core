<div class="block">
    <nav id="{{ $id }}Tabs" class="flex space-x-4" aria-label="Tabs">
        @foreach($tabs as $tab)
        <a
            href="#"
            data-target="{{ $tab['target'] }}"
            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-70"
            >{{ $tab["label"] }}</a
        >
        @endforeach
    </nav>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
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

            // Here you would show the corresponding content
            document
                .querySelectorAll(".{{ $id }}Content")
                .forEach((content) => content.classList.add("hidden"));
            activeContent.classList.remove("hidden");
        }

        // Set initial active tab based on URL hash
        setActiveState(window.location.hash?.substring(1));

        // Handle tab clicks
        tabs.forEach((tab) => {
            tab.addEventListener("click", function (e) {
                const isUrl = selectedValue.startsWith("http");

                if (isUrl) {
                    return;
                } else {
                    e.preventDefault();
                    const hash = this.getAttribute("data-target");
                    history.pushState(null, null, "#" + hash);
                    setActiveState(hash);
                }
            });
        });

        // Listen for hash changes (browser back/forward buttons)
        window.addEventListener("hashchange", function () {
            setActiveState(window.location.hash?.substring(1));
        });
    });
</script>
