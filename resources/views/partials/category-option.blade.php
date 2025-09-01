<option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
    {{ str_repeat('— ', $level) }}{{ $category->name }}
</option>

@if($category->children && $category->children->count() > 0)
    @foreach($category->children as $child)
        @include('partials.category-option', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif