<table class="m-2">
    <thead><tr><th>id</th><th>name</th></tr></thead>
    <tbody>
    @foreach ($users as $user)
    <tr><td>{{ $user->id }}</td><td>{{ $user->name }}</tr>
    @endforeach
    </tbody>
</table>