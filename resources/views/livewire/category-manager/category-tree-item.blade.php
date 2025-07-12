@props(['category', 'level' => 0])

<div class="space-y-2">
    {{-- Current Category --}}
    <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800">
        <div class="flex items-center gap-3" style="margin-left: {{ $level * 24 }}px">
            {{-- Indentation indicator --}}
            @if($level > 0)
                <div class="w-4 h-px bg-zinc-300 dark:bg-zinc-600"></div>
            @endif
            
            {{-- Category visual indicators --}}
            @if($category->color)
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></div>
            @endif
            @if($category->icon)
                <flux:icon :name="$category->icon" class="w-4 h-4 text-zinc-600 dark:text-zinc-400" />
            @endif
            
            {{-- Category name and stats --}}
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-medium">{{ $category->name }}</span>
                    <span class="text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                        {{ $category->transactions()->count() }} transactions
                    </span>
                    @if($category->children->count() > 0)
                        <span class="text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                            {{ $category->children->count() }} subcategories
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Actions --}}
        <div class="flex gap-2">
            <flux:button 
                wire:click="openCategoryForm({{ $category->id }})" 
                size="sm" 
                variant="ghost" 
                icon="pencil"
            >
                Edit
            </flux:button>
            <flux:button 
                wire:click="deleteCategory({{ $category->id }})" 
                wire:confirm="Are you sure you want to delete this category?"
                size="sm" 
                variant="ghost" 
                icon="trash"
                class="text-red-600 hover:text-red-700"
            >
                Delete
            </flux:button>
        </div>
    </div>
    
    {{-- Children Categories --}}
    @if($category->children->count() > 0)
        @foreach($category->children as $child)
            @include('livewire.category-manager.category-tree-item', ['category' => $child, 'level' => $level + 1])
        @endforeach
    @endif
</div>