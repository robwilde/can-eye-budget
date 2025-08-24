<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Confirm Import
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            Review the import details and confirm to start processing your {{ $totalRows }} transactions.
        </p>
    </div>

    {{-- Import Summary Card --}}
    <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900 dark:to-purple-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Import Summary
                </h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    Ready to process your CSV file
                </p>
            </div>
            <flux:icon.document-check class="h-8 w-8 text-blue-600 dark:text-blue-400" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">File:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $csvFile ? $csvFile->getClientOriginalName() : 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Account:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $this->getSelectedAccountProperty->name ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Account Type:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                        {{ $this->getSelectedAccountProperty->type ?? 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Total Rows:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format($totalRows) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">File Size:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $csvFile ? number_format($csvFile->getSize() / 1024, 1) . ' KB' : 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Mapped Columns:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ count(array_filter($columnMapping)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Options --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Import Processing
        </h3>
        
        <div class="space-y-4">
            <div class="flex items-start space-x-3">
                <flux:icon.shield-check class="h-5 w-5 text-green-500 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        Duplicate Detection
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        We'll automatically check for duplicate transactions and flag any potential matches for your review.
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <flux:icon.tag class="h-5 w-5 text-blue-500 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        Auto-Categorization
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Transactions will be automatically categorized based on your existing rules and patterns.
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <flux:icon.clock class="h-5 w-5 text-yellow-500 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        Processing Time
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $totalRows > 1000 
                            ? 'Large file detected - import will be processed in the background and you\'ll be notified when complete.'
                            : 'Small file - import will be processed immediately and should complete within a few seconds.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Column Mapping Review --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Final Column Mapping</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($columnMapping as $field => $column)
                @if($column)
                    <div class="bg-white dark:bg-gray-700 rounded p-3 border border-gray-200 dark:border-gray-600">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                            {{ str_replace('_', ' ', $field) }}
                        </div>
                        <div class="text-sm text-gray-900 dark:text-white font-medium">
                            {{ $column }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Warning for Large Files --}}
    @if($totalRows > 1000)
        <div class="bg-amber-50 dark:bg-amber-900 border border-amber-200 dark:border-amber-700 rounded p-4">
            <div class="flex">
                <flux:icon.exclamation-triangle class="w-5 h-5 text-amber-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        Large File Processing
                    </h3>
                    <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                        Your file contains {{ number_format($totalRows) }} rows and will be processed in the background. 
                        You can close this page and check the import history later for results.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex justify-end pt-6">
        <flux:button 
            variant="primary"
            size="lg"
            wire:click="processImport"
            wire:loading.attr="disabled"
            wire:target="processImport">
            
            <span wire:loading.remove wire:target="processImport" class="flex items-center">
                <flux:icon.play class="w-4 h-4 mr-2" />
                Start Import ({{ number_format($totalRows) }} rows)
            </span>
            <span wire:loading wire:target="processImport" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing Import...
            </span>
        </flux:button>
    </div>
</div>