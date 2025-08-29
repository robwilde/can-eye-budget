<div class="space-y-6">
    <div class="text-center">
        @if(isset($importResult['success']) && $importResult['success'])
            <flux:icon.check-circle class="mx-auto h-16 w-16 text-green-500 mb-4" />
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Import Successful!
            </h2>
            <p class="text-gray-600 dark:text-gray-300">
                Your CSV file has been successfully imported and transactions have been created.
            </p>
        @else
            <flux:icon.x-circle class="mx-auto h-16 w-16 text-red-500 mb-4" />
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Import Failed
            </h2>
            <p class="text-gray-600 dark:text-gray-300">
                There was an error processing your CSV file. Please try again or contact support.
            </p>
        @endif
    </div>

    @if(isset($importResult['success']) && $importResult['success'])
        {{-- Success Results --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                    {{ $importResult['created_count'] ?? 0 }}
                </div>
                <div class="text-sm text-green-700 dark:text-green-300">
                    Transactions Created
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $importResult['total_rows'] ?? 0 }}
                </div>
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    Total Rows Processed
                </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                    {{ $importResult['duplicate_count'] ?? 0 }}
                </div>
                <div class="text-sm text-yellow-700 dark:text-yellow-300">
                    Duplicates Skipped
                </div>
            </div>
        </div>

        {{-- Import Details --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Import Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Import ID:</span>
                        <span class="text-sm font-mono text-gray-900 dark:text-white">
                            #{{ $import->id ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">File Name:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ $import->filename ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Account:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ $this->getSelectedAccountProperty->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Imported At:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ $import && $import->imported_at ? $import->imported_at->format('M j, Y g:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Success Rate:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            @if(isset($importResult['total_rows']) && $importResult['total_rows'] > 0)
                                {{ round((($importResult['created_count'] ?? 0) / $importResult['total_rows']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            {{ $import->status ?? 'completed' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Duplicate Information --}}
        @if(isset($importResult['duplicate_count']) && $importResult['duplicate_count'] > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded p-4">
                <div class="flex">
                    <flux:icon.information-circle class="w-5 h-5 text-yellow-400 mt-0.5" />
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Duplicate Transactions Detected
                        </h3>
                        <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            {{ $importResult['duplicate_count'] }} transactions were identified as potential duplicates and were skipped. 
                            This helps prevent importing the same transactions multiple times.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Next Steps --}}
        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded p-4">
            <div class="flex">
                <flux:icon.light-bulb class="w-5 h-5 text-blue-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        What's Next?
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Review your transactions on the dashboard</li>
                            <li>Check auto-categorization results and adjust if needed</li>
                            <li>Set up category rules for future imports</li>
                            <li>Reconcile with your bank statements</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Error Results --}}
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded p-4">
            <div class="flex">
                <flux:icon.exclamation-triangle class="w-5 h-5 text-red-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        Import Error
                    </h3>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                        {{ $importResult['error'] ?? $errorMessage ?? 'An unknown error occurred during import.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Troubleshooting Tips --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Troubleshooting Tips</h4>
            <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                <li>• Ensure your CSV file has proper headers</li>
                <li>• Check that date columns are in a recognized format</li>
                <li>• Verify amount columns contain numeric values</li>
                <li>• Make sure the file isn't corrupted or empty</li>
                <li>• Try importing a smaller subset of the data</li>
            </ul>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 pt-6">
        @if(isset($importResult['success']) && $importResult['success'])
            <flux:button variant="primary" href="{{ route('dashboard') }}" class="flex-1">
                <flux:icon.chart-bar class="w-4 h-4 mr-2" />
                View Dashboard
            </flux:button>
            <flux:button variant="outline" wire:click="startOver" class="flex-1">
                <flux:icon.arrow-path class="w-4 h-4 mr-2" />
                Import Another File
            </flux:button>
            @if(isset($importResult['duplicate_count']) && $importResult['duplicate_count'] > 0)
                <flux:button variant="ghost" href="#" class="flex-1">
                    <flux:icon.eye class="w-4 h-4 mr-2" />
                    Review Duplicates
                </flux:button>
            @endif
        @else
            <flux:button variant="primary" wire:click="startOver" class="flex-1">
                <flux:icon.arrow-path class="w-4 h-4 mr-2" />
                Try Again
            </flux:button>
            <flux:button variant="outline" href="{{ route('dashboard') }}" class="flex-1">
                <flux:icon.home class="w-4 h-4 mr-2" />
                Back to Dashboard
            </flux:button>
        @endif
    </div>
</div>