@if($model->stickleRelationships([\Illuminate\Database\Eloquent\Relations\BelongsTo::class])->count()
> 0) @php $belongsToRelationship =
$model->stickleRelationships([\Illuminate\Database\Eloquent\Relations\BelongsTo::class])->first()->name;
@endphp @if($parent = $model->$belongsToRelationship)
<h2 class="text-sm text-gray-500">
    <a
        href="{{ route('stickle::model', ['modelClass' => class_basename($parent), 'uid' => $parent->id]) }}"
        class="text-gray-500 hover:text-gray-700"
        >{{ $parent->name }}
    </a>
</h2>
@endif @endif
