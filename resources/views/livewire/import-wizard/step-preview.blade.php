<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Preview Import Data
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            Review the first 10 transactions to ensure the data is being parsed correctly.
        </p>
    </div>

    {{-- Import Summary --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $totalRows }}
                </div>
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    Total Rows
                </div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ count($previewData) }}
                </div>
                <div class="text-sm text-green-700 dark:text-green-300">
                    Preview Rows
                </div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $this->getSelectedAccountProperty->name ?? 'N/A' }}
                </div>
                <div class="text-sm text-purple-700 dark:text-purple-300">
                    Target Account
                </div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                    {{ count(array_filter($columnMapping)) }}
                </div>
                <div class="text-sm text-orange-700 dark:text-orange-300">
                    Mapped Columns
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Data Table --}}
    @if(count($previewData) > 0)
        <div class="overflow-hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                    Transaction Preview (First 10 rows)
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Amount
                            </th>
                            @if(isset($previewData[0]['balance']))
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Balance
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($previewData as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ isset($row['date']) ? $row['date']->format('M j, Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                        {{ $row['type'] === 'income' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $row['type'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    <div class="truncate max-w-xs" title="{{ $row['description'] ?? 'N/A' }}">
                                        {{ $row['description'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap
                                    {{ $row['type'] === 'income' 
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400' }}">
                                    ${{ number_format($row['amount'] ?? 0, 2) }}
                                </td>
                                @if(isset($row['balance']))
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right whitespace-nowrap">
                                        ${{ number_format($row['balance'], 2) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <flux:icon.exclamation-triangle class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No preview data</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Unable to parse any transactions from the CSV file.
            </p>
        </div>
    @endif

    {{-- Column Mapping Summary --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Applied Column Mapping</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($columnMapping as $field => $column)
                @if($column)
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-600 dark:text-gray-300 capitalize">
                            {{ str_replace('_', ' ', $field) }}:
                        </span>
                        <span class="text-gray-900 dark:text-white">
                            {{ $column }}
                        </span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Validation Warnings --}}
    @if(count($previewData) === 0)
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded p-4">
            <div class="flex">
                <flux:icon.exclamation-triangle class="w-5 h-5 text-red-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        No Valid Data Found
                    </h3>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                        We couldn't parse any valid transactions from your CSV file. Please go back and check your column mappings.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded p-4">
            <div class="flex">
                <flux:icon.check-circle class="w-5 h-5 text-green-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        Data Looks Good!
                    </h3>
                    <p class="mt-2 text-sm text-green-700 dark:text-green-300">
                        We successfully parsed {{ count($previewData) }} sample transactions. 
                        If the preview looks correct, continue to confirm the import.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex justify-end pt-4">
        <flux:button 
            variant="primary" 
            wire:click="processPreview"
            :disabled="count($previewData) === 0">
            Continue to Confirmation
        </flux:button>
    </div>
</div>