<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            Map CSV Columns
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            Tell us which columns contain your transaction data. We've made our best guess based on the column headers.
        </p>
    </div>

    {{-- Column Mapping Form --}}
    <div class="grid md:grid-cols-2 gap-6">
        {{-- Required Fields --}}
        <div class="space-y-4">
            <h3 class="font-medium text-gray-900 dark:text-white">Required Fields</h3>
            
            {{-- Date Column --}}
            <flux:field>
                <flux:label for="date">Transaction Date *</flux:label>
                <flux:select wire:model.live="columnMapping.date" placeholder="Select date column...">
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="columnMapping.date" />
            </flux:field>

            {{-- Description Column --}}
            <flux:field>
                <flux:label for="description">Description *</flux:label>
                <flux:select wire:model.live="columnMapping.description" placeholder="Select description column...">
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="columnMapping.description" />
            </flux:field>

            {{-- Amount Column --}}
            <flux:field>
                <flux:label for="amount">Single Amount Column</flux:label>
                <flux:select wire:model.live="columnMapping.amount" placeholder="Select amount column (if single)...">
                    <option value="">None - Using separate Debit/Credit columns</option>
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="columnMapping.amount" />
            </flux:field>
        </div>

        {{-- Optional Fields --}}
        <div class="space-y-4">
            <h3 class="font-medium text-gray-900 dark:text-white">Optional Fields</h3>
            
            {{-- Debit Column --}}
            <flux:field>
                <flux:label for="debit">Debit/Withdrawal Column</flux:label>
                <flux:select wire:model.live="columnMapping.debit" placeholder="Select debit column...">
                    <option value="">None</option>
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            {{-- Credit Column --}}
            <flux:field>
                <flux:label for="credit">Credit/Deposit Column</flux:label>
                <flux:select wire:model.live="columnMapping.credit" placeholder="Select credit column...">
                    <option value="">None</option>
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            {{-- Balance Column --}}
            <flux:field>
                <flux:label for="balance">Balance Column</flux:label>
                <flux:select wire:model.live="columnMapping.balance" placeholder="Select balance column...">
                    <option value="">None</option>
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            {{-- Entered Date Column --}}
            <flux:field>
                <flux:label for="entered_date">Entered/Processed Date</flux:label>
                <flux:select wire:model.live="columnMapping.entered_date" placeholder="Select entered date column...">
                    <option value="">None</option>
                    @foreach($csvHeaders as $index => $header)
                        <option value="{{ $header }}">{{ $header }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    {{-- CSV Headers Preview --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Detected CSV Headers</h4>
        <div class="flex flex-wrap gap-2">
            @foreach($csvHeaders as $index => $header)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ in_array($header, array_filter($columnMapping)) 
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                    {{ $header }}
                    @if(in_array($header, array_filter($columnMapping)))
                        <flux:icon.check class="ml-1 w-3 h-3" />
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    {{-- Validation Info --}}
    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded p-4">
        <div class="flex">
            <flux:icon.information-circle class="w-5 h-5 text-blue-400 mt-0.5" />
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Column Mapping Rules
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Date and Description columns are required</li>
                        <li>You must map either a single Amount column OR both Debit and Credit columns</li>
                        <li>Positive amounts are treated as income, negative as expenses</li>
                        <li>Balance column is optional and used for reconciliation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end pt-4">
        <flux:button 
            variant="primary" 
            wire:click="processMapping"
            wire:loading.attr="disabled"
            wire:target="processMapping">
            
            <span wire:loading.remove wire:target="processMapping">
                Continue to Preview
            </span>
            <span wire:loading wire:target="processMapping" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        </flux:button>
    </div>
</div>