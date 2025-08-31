<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Upload Your CSV File
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            Select the account and upload your bank statement or transaction CSV file.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Account Selection --}}
        <div>
            <flux:field>
                <flux:label for="account">Select Account *</flux:label>
                <flux:select wire:model.live="selectedAccountId">
                    <option value="">Choose an account...</option>
                    @foreach($this->user_accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->name }} ({{ $account->type }})
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="selectedAccountId" />
            </flux:field>
            
            @if($this->selected_account)
                <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900 rounded text-sm">
                    <div class="font-medium text-blue-900 dark:text-blue-100">
                        {{ $this->selected_account->name }}
                    </div>
                    <div class="text-blue-700 dark:text-blue-200">
                        Current Balance: ${{ number_format($this->selected_account->getCurrentBalance(), 2) }}
                    </div>
                </div>
            @endif
        </div>

        {{-- File Upload --}}
        <div>
            <flux:field>
                <flux:label for="csvFile">CSV File *</flux:label>
                <div class="mt-1">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            @if($csvFile)
                                <flux:icon.document class="w-8 h-8 mb-2 text-green-500" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">{{ $csvFile->getClientOriginalName() }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($csvFile->getSize() / 1024, 1) }} KB
                                </p>
                            @else
                                <flux:icon.cloud-arrow-up class="w-8 h-8 mb-2 text-gray-400" />
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    CSV or TXT files (MAX. 10MB)
                                </p>
                            @endif
                        </div>
                        <input wire:model="csvFile" type="file" class="hidden" accept=".csv,.txt" />
                    </label>
                </div>
                <flux:error name="csvFile" />
            </flux:field>
        </div>
    </div>

    {{-- File Format Information --}}
    <div class="bg-amber-50 dark:bg-amber-900 border border-amber-200 dark:border-amber-700 rounded p-4">
        <div class="flex">
            <flux:icon.information-circle class="w-5 h-5 text-amber-400 mt-0.5" />
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    Supported File Formats
                </h3>
                <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>CSV files exported from online banking</li>
                        <li>Files should include columns for: Date, Description, Amount (or Debit/Credit)</li>
                        <li>Optional columns: Balance, Reference, Category</li>
                        <li>Maximum file size: 10MB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end pt-4">
        <flux:button 
            variant="primary" 
            wire:click="processUpload" 
            :disabled="!$csvFile || $selectedAccountId === ''"
            wire:loading.attr="disabled"
            wire:target="processUpload">
            
            <span wire:loading.remove wire:target="processUpload">
                Continue to Column Mapping
            </span>
            <span wire:loading wire:target="processUpload" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        </flux:button>
    </div>
</div>