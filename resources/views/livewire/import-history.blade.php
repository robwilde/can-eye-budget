<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Import History
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">
                        View and manage your CSV import history
                    </p>
                </div>
                <flux:button variant="primary" href="{{ route('import.wizard') }}">
                    <flux:icon.plus class="size-4 mr-2" />
                    New Import
                </flux:button>
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="mb-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                @foreach(['all' => 'All', 'completed' => 'Completed', 'failed' => 'Failed', 'processing' => 'Processing', 'pending' => 'Pending'] as $status => $label)
                    <button 
                        wire:click="setStatusFilter('{{ $status }}')"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors
                            {{ $statusFilter === $status 
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        {{ $label }}
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs
                            {{ $statusFilter === $status 
                                ? 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-200' 
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $statusCounts[$status] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Imports Table --}}
        @if($imports->count() > 0)
            <flux:card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('filename')">
                                    <div class="flex items-center space-x-1">
                                        <span>File Name</span>
                                        @if($sortBy === 'filename')
                                            <flux:icon.chevron-up class="size-3 {{ $sortDirection === 'asc' ? 'rotate-0' : 'rotate-180' }}" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('imported_at')">
                                    <div class="flex items-center space-x-1">
                                        <span>Import Date</span>
                                        @if($sortBy === 'imported_at')
                                            <flux:icon.chevron-up class="size-3 {{ $sortDirection === 'asc' ? 'rotate-0' : 'rotate-180' }}" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('status')">
                                    <div class="flex items-center space-x-1">
                                        <span>Status</span>
                                        @if($sortBy === 'status')
                                            <flux:icon.chevron-up class="size-3 {{ $sortDirection === 'asc' ? 'rotate-0' : 'rotate-180' }}" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Transactions
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Success Rate
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($imports as $import)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <flux:icon.document class="size-5 text-gray-400 mr-3" />
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $import->filename }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    ID: #{{ $import->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $import->imported_at ? $import->imported_at->format('M j, Y g:i A') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                            {{ $import->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                               ($import->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                               ($import->status === 'processing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                               'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                                            {{ $import->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ $import->matched_count }}/{{ $import->row_count }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $import->row_count - $import->matched_count }} skipped
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $import->getMatchPercentageAttribute() }}%
                                        </div>
                                        @if($import->getMatchPercentageAttribute() === 100)
                                            <flux:icon.check-circle class="size-4 text-green-500 mx-auto" />
                                        @elseif($import->getMatchPercentageAttribute() > 80)
                                            <flux:icon.exclamation-triangle class="size-4 text-yellow-500 mx-auto" />
                                        @else
                                            <flux:icon.x-circle class="size-4 text-red-500 mx-auto" />
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        {{-- Download CSV --}}
                                        @if($import->csv_file_path)
                                            <flux:button size="sm" variant="ghost" wire:click="downloadCsvFile({{ $import->id }})" title="Download CSV">
                                                <flux:icon.arrow-down-tray class="size-4" />
                                            </flux:button>
                                        @endif

                                        {{-- Reprocess Failed Import --}}
                                        @if($import->status === 'failed')
                                            <flux:button size="sm" variant="outline" wire:click="reprocessImport({{ $import->id }})" title="Retry Import">
                                                <flux:icon.arrow-path class="size-4" />
                                            </flux:button>
                                        @endif

                                        {{-- Delete Import --}}
                                        <flux:button 
                                            size="sm" 
                                            variant="ghost"
                                            wire:click="deleteImport({{ $import->id }})"
                                            wire:confirm="Are you sure you want to delete this import and all associated transactions?"
                                            title="Delete Import">
                                            <flux:icon.trash class="size-4 text-red-500" />
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($imports->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $imports->links() }}
                    </div>
                @endif
            </flux:card>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <flux:icon.document-text class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    @if($statusFilter === 'all')
                        No imports yet
                    @else
                        No {{ $statusFilter }} imports
                    @endif
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if($statusFilter === 'all')
                        Get started by importing your first CSV file.
                    @else
                        Try selecting a different status filter.
                    @endif
                </p>
                @if($statusFilter === 'all')
                    <flux:button variant="primary" href="{{ route('import.wizard') }}">
                        <flux:icon.plus class="size-4 mr-2" />
                        Import CSV File
                    </flux:button>
                @else
                    <flux:button variant="outline" wire:click="setStatusFilter('all')">
                        View All Imports
                    </flux:button>
                @endif
            </div>
        @endif
    </div>
</div>