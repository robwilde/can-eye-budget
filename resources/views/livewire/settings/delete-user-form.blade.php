<div>
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <section class="space-y-6">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Delete Account') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                    </p>
                </header>

                <flux:button variant="danger" wire:click="confirmUserDeletion">
                    {{ __('Delete Account') }}
                </flux:button>

                <!-- Simple form for testing - modal functionality would need proper modal setup -->
                @if($showDeleteConfirmation ?? false)
                <div class="mt-4 p-4 border border-red-300 rounded-lg">
                    <form wire:submit="deleteUser">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Are you sure?') }}
                        </h3>

                        <div class="mt-4">
                            <flux:field>
                                <flux:label class="sr-only">{{ __('Password') }}</flux:label>
                                <flux:input
                                    name="password"
                                    type="password"
                                    wire:model="password"
                                    placeholder="{{ __('Password') }}"
                                />
                                <flux:error name="password" />
                            </flux:field>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <flux:button type="button" variant="ghost" wire:click="$set('showDeleteConfirmation', false)">
                                {{ __('Cancel') }}
                            </flux:button>

                            <flux:button type="submit" variant="danger">
                                {{ __('Delete Account') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
                @endif
            </section>
        </div>
    </div>
</div>