<x-stickle-ui-default-layout>
    <div class="w-full flex flex-col md:flex-row">
        <!-- Navigation for medium screens -->
        <div class="md:hidden flex justify-left space-x-4 p-4">
            <button class="tab-button" data-target="#column1">
                {{ $model }}
            </button>
            <button class="tab-button" data-target="#column2">
                Statistics
            </button>
            <button class="tab-button" data-target="#column3">Events</button>
        </div>

        <!-- 2/3 Column -->
        <div id="column1" class="w-full md:w-3/5 p-4 md:block">
            <div class="bg-white p-6 shadow-md">
                <x-stickle-models-table
                    :heading="\Illuminate\Support\Str::of($model)->headline()"
                    :subheading="sprintf('A full list of your %s.', \Illuminate\Support\Str::of($model)->plural())"
                    :model="$model"
                >
                </x-stickle-models-table>
            </div>
        </div>

        <!-- 1/3 Column -->
        <div id="column2" class="w-full md:w-2/5 p-4 md:block hidden">
            <!-- Column 2 content here -->
            <x-stickle-models-chartlist :model="$model">
            </x-stickle-models-chartlist>
        </div>
    </div>
</x-stickle-ui-default-layout>

<script>
    document.querySelectorAll(".tab-button").forEach((button) => {
        button.addEventListener("click", () => {
            document
                .querySelectorAll(".tab-button")
                .forEach((btn) => btn.classList.remove("active"));
            button.classList.add("active");

            document
                .querySelectorAll(".md\\:block")
                .forEach((column) => column.classList.add("hidden"));
            document
                .querySelector(button.getAttribute("data-target"))
                .classList.remove("hidden");
        });
    });
</script>
