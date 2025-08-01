<x-demo-default-layout>
    <div>
        <h1 class="text-center py-2 border-b-2">Your admin</h1>
        <section class="p-2">
            @include('stickle::demo/components/user-table', ['users' => []])
        </section>
    </div>
</x-demo-default-layout>