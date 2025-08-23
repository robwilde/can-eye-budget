<div>
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <section>
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Update Password') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Ensure your account is using a long, random password to stay secure.') }}
                    </p>
                </header>

                <form wire:submit="updatePassword" class="mt-6 space-y-6">
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Current Password') }}</flux:label>
                            <flux:input 
                                name="current_password"
                                type="password" 
                                wire:model="current_password" 
                                autocomplete="current-password" 
                            />
                            <flux:error name="current_password" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>{{ __('New Password') }}</flux:label>
                            <flux:input 
                                name="password"
                                type="password" 
                                wire:model="password" 
                                autocomplete="new-password" 
                            />
                            <flux:error name="password" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>{{ __('Confirm Password') }}</flux:label>
                            <flux:input 
                                name="password_confirmation"
                                type="password" 
                                wire:model="password_confirmation" 
                                autocomplete="new-password" 
                            />
                            <flux:error name="password_confirmation" />
                        </flux:field>
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button type="submit">{{ __('Save') }}</flux:button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" 
                               x-show="show" 
                               x-transition 
                               x-init="setTimeout(() => show = false, 2000)" 
                               class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>