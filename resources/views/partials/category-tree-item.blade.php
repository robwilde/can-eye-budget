<div class="flex items-center justify-between p-3 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 {{ $level > 0 ? 'ml-' . ($level * 6) . ' border-l-2 border-zinc-200 dark:border-zinc-600' : '' }}">
    <div class="flex items-center space-x-3">
        @if($category->color)
            <div 
                class="w-4 h-4 rounded-full flex-shrink-0" 
                style="background-color: {{ $category->color }}"
            ></div>
        @endif
        
        <div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ $category->name }}
            </div>
            @if($level === 0 && $category->children->count() > 0)
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $category->children->count() }} subcategories
                </div>
            @endif
        </div>
    </div>

    <div class="flex items-center space-x-3">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ $category->transactions_count ?? 0 }} transactions
        </div>
        
        @if($category->transactions_count === 0 || !$category->transactions_count)
            <flux:button 
                variant="ghost" 
                size="sm" 
                wire:click="deleteCategory({{ $category->id }})" 
                icon="trash"
                onclick="return confirm('Are you sure you want to delete this category?')"
            >
            </flux:button>
        @else
            <div class="w-8 h-8"></div>
        @endif
    </div>
</div>

@if($category->children && $category->children->count() > 0)
    @foreach($category->children as $child)
        @include('partials.category-tree-item', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif