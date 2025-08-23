<div>
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <section>
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Profile Information') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Update your account's profile information and email address.") }}
                    </p>
                </header>

                <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Name') }}</flux:label>
                            <flux:input 
                                name="name" 
                                wire:model="name" 
                                required 
                                autofocus 
                                autocomplete="name" 
                            />
                            <flux:error name="name" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>{{ __('Email') }}</flux:label>
                            <flux:input 
                                name="email" 
                                type="email"
                                wire:model="email" 
                                required 
                                autocomplete="username" 
                            />
                            <flux:error name="email" />
                        </flux:field>
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button type="submit">{{ __('Save') }}</flux:button>

                        @if (session('status') === 'profile-updated')
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