<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Import CSV Transactions
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">
                Import bank statements and CSV files to automatically create transactions
            </p>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-8">
                    @foreach($steps as $index => $step)
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                                    {{ $this->current_step_index >= $index 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $index + 1 }}
                                </div>
                                <span class="ml-2 text-sm font-medium capitalize
                                    {{ $this->current_step_index >= $index 
                                        ? 'text-blue-600 dark:text-blue-400' 
                                        : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ ucfirst($step) }}
                                </span>
                            </div>
                            @if($index < count($steps) - 1)
                                <div class="w-12 h-px mx-4
                                    {{ $this->current_step_index > $index 
                                        ? 'bg-blue-600' 
                                        : 'bg-gray-200 dark:bg-gray-700' }}">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ round($this->progress_percentage) }}% Complete
                </div>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ $this->progress_percentage }}%">
                </div>
            </div>
        </div>

        {{-- Error Messages --}}
        @if($errorMessage)
            <div class="mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 px-4 py-3 rounded">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- Step Content --}}
        <flux:card class="mb-8">
            @if($currentStep === 'upload')
                @include('livewire.import-wizard.step-upload')
            @elseif($currentStep === 'mapping')
                @include('livewire.import-wizard.step-mapping')
            @elseif($currentStep === 'preview')
                @include('livewire.import-wizard.step-preview')
            @elseif($currentStep === 'confirm')
                @include('livewire.import-wizard.step-confirm')
            @elseif($currentStep === 'results')
                @include('livewire.import-wizard.step-results')
            @endif
        </flux:card>

        {{-- Navigation Buttons --}}
        <div class="flex justify-between items-center">
            <div>
                @if($currentStep !== 'upload' && $currentStep !== 'results')
                    <flux:button variant="outline" wire:click="previousStep">
                        <flux:icon.chevron-left class="size-4" />
                        Previous
                    </flux:button>
                @endif
            </div>

            <div class="flex gap-3">
                @if($currentStep === 'results')
                    <flux:button variant="primary" wire:click="startOver">
                        Import Another File
                    </flux:button>
                    <flux:button variant="outline" href="{{ route('home') }}">
                        Go to Dashboard
                    </flux:button>
                @else
                    <flux:button variant="outline" wire:click="startOver">
                        Cancel
                    </flux:button>
                @endif
            </div>
        </div>
    </div>
</div>