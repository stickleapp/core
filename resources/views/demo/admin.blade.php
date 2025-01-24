<x-blank-layout>
    <div>
        <h1 class="text-center py-2 border-b-2">Your admin</h1>
        <section class="p-2">
            @include('STICKLE::demo/components/user-table', ['users' => $users])
        </section>
    </div>
</x-blank-layout>