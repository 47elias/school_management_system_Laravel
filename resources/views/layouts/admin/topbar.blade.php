<!-- resources/views/layouts/admin/topbar.blade.php -->
<header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 py-2.5 shadow-sm transition-colors duration-200">
    
    <!-- Left Section: Sidebar Toggle & Brand Title -->
    <div class="flex items-center space-x-3">
        <button @click="sidebarOpen = !sidebarOpen" 
                type="button"
                title="Toggle Sidebar"
                class="text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        
        <div class="text-slate-800 dark:text-slate-100 font-bold text-base sm:text-lg tracking-tight hidden sm:block">
            {{ config('app.name', 'School Portal') }}
        </div>
    </div>

    <!-- Right Section: Toolbar Action Controls -->
    <div class="flex items-center space-x-2 sm:space-x-3">
        
        <!-- 1. FULL SCREEN BUTTON (100% Native Inline SVG & JS Logic) -->
        <button @click="toggleFullscreen()" 
                type="button"
                title="Toggle Full Screen"
                class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700 shadow-sm focus:outline-none">
            
            <template x-if="!isFullscreen">
                <div class="flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4"></path>
                    </svg>
                    <span class="inline">Full Screen</span>
                </div>
            </template>
            
            <template x-if="isFullscreen">
                <div class="flex items-center space-x-1.5 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="inline">Exit Full Screen</span>
                </div>
            </template>
        </button>

        <!-- 2. THEME SWITCHER BUTTON & DROPDOWN -->
        <div class="relative" x-data="{ themeOpen: false }" @click.away="themeOpen = false">
            <button @click="themeOpen = !themeOpen" 
                    type="button"
                    title="Change Visual Theme"
                    class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700 shadow-sm focus:outline-none">
                
                <!-- Active Theme Icon -->
                <template x-if="theme === 'light'">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </template>
                <template x-if="theme === 'dark'">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </template>
                <template x-if="theme === 'system'">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </template>

                <!-- Active Theme Text Label -->
                <span class="capitalize" x-text="theme === 'system' ? 'Theme: System' : (theme === 'dark' ? 'Theme: Dark' : 'Theme: Light')"></span>
                
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Theme Switcher Options Dropdown -->
            <div x-show="themeOpen" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50">
                
                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                    Select Display Theme
                </div>

                <!-- System Default Mode Option -->
                <button @click="setTheme('system'); themeOpen = false" 
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        System Default
                    </div>
                    <svg x-show="theme === 'system'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>

                <!-- Light Mode Option -->
                <button @click="setTheme('light'); themeOpen = false" 
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Light Mode
                    </div>
                    <svg x-show="theme === 'light'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>

                <!-- Dark Mode Option -->
                <button @click="setTheme('dark'); themeOpen = false" 
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        Dark Mode
                    </div>
                    <svg x-show="theme === 'dark'" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="h-5 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

        <!-- 3. NOTIFICATIONS BUTTON -->
        <div class="relative" x-data="{ notificationsOpen: false }" @click.away="notificationsOpen = false">
            <button @click="notificationsOpen = !notificationsOpen" 
                    type="button"
                    title="Notifications"
                    class="text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
            </button>

            <!-- Notifications Panel -->
            <div x-show="notificationsOpen" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Notifications</span>
                    <span class="text-[10px] bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-full font-bold">1 New</span>
                </div>
                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="flex items-start">
                            <div class="bg-blue-100 dark:bg-blue-900/40 p-2 rounded-full mr-3 text-blue-600 dark:text-blue-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">System Ready</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Theme & Full Screen controls active.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. USER PROFILE -->
        <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
            <button @click="profileOpen = !profileOpen" 
                    type="button"
                    class="flex items-center space-x-2 focus:outline-none p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <img src="{{ asset('adminlte/dist/img/avatar5.png') }}" alt="User Avatar" class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 object-cover">
                <div class="hidden md:flex flex-col text-left">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 leading-tight">{{ Auth::user()?->name ?? 'Administrator' }}</span>
                    <span class="text-[10px] text-slate-400 leading-tight">{{ ucfirst(Auth::user()?->role ?? 'Admin') }}</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Profile Panel -->
            <div x-show="profileOpen" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-50">
                <a href="{{ route('admin.profile') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    My Profile
                </a>
                <a href="{{ route('admin.change_password') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Security
                </a>
                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center px-4 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>