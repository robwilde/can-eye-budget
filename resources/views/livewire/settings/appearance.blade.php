<div>
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <section>
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Appearance') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Customize the appearance of your application.') }}
                    </p>
                </header>

                <div class="mt-6 space-y-6">
                    <div>
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-3">
                            {{ __('Theme') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('Select your preferred color scheme.') }}
                        </p>

                        <div class="flex gap-4"
                             x-data="{
                                theme: localStorage.getItem('theme') || 'light',
                                setTheme(newTheme) {
                                    console.log('Setting theme to:', newTheme);
                                    this.theme = newTheme;
                                    localStorage.setItem('theme', newTheme);
                                    if (newTheme === 'dark') {
                                        document.documentElement.classList.add('dark');
                                    } else {
                                        document.documentElement.classList.remove('dark');
                                    }
                                }
                             }"
                             x-init="console.log('Initial theme:', theme)">
                            <button
                                type="button"
                                @click="setTheme('light')"
                                :class="theme === 'light' ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                                class="flex-1 p-4 border-2 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Light</span>
                                    <span x-show="theme === 'light'" class="ml-2 text-blue-600 dark:text-blue-400">✓</span>
                                </div>
                            </button>

                            <button
                                type="button"
                                @click="setTheme('dark')"
                                :class="theme === 'dark' ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                                class="flex-1 p-4 border-2 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Dark</span>
                                    <span x-show="theme === 'dark'" class="ml-2 text-blue-600 dark:text-blue-400">✓</span>
                                </div>
                            </button>
                        </div>

                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Your theme preference is saved automatically and will persist across all pages.') }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
