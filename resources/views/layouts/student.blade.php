<!DOCTYPE html>
<html lang="en" 
      x-data="{ 
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          sidebarOpen: false, 
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
          }
      }"
      :class="{ 'dark': darkMode }"
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Student Portal | {{ config('app.name', 'SMS') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="h-full font-sans text-xs text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-950 antialiased selection:bg-slate-900 selection:text-white transition-colors duration-150">

    <div class="min-h-screen flex bg-slate-100 dark:bg-slate-950">

        <!-- MOBILE SIDEBAR BACKDROP -->
        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs lg:hidden"></div>

        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 transform lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col justify-between border-r border-slate-800">
            
            <div>
                <!-- LOGO HEADER -->
                <div class="h-14 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800">
                    <a href="{{ route('student.dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div class="leading-tight">
                            <span class="text-xs font-extrabold tracking-tight text-white block">STUDENT<span class="text-emerald-400">PORTAL</span></span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Academic System</span>
                        </div>
                    </a>

                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- USER CARD -->
                <div class="p-3 mx-3 my-3 rounded-lg bg-slate-800 border border-slate-700 flex items-center space-x-2.5">
                    <img src="{{ Auth::guard('student')->user()->gender == 'Female' ? asset('adminlte/dist/img/avatar3.png') : asset('adminlte/dist/img/avatar5.png') }}" 
                         class="w-8 h-8 rounded-md object-cover ring-2 ring-emerald-500 bg-slate-700" 
                         alt="Avatar">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-white truncate">{{ Auth::guard('student')->user()->name }}</h4>
                        <p class="text-[10px] text-emerald-400 font-bold truncate">Active Student</p>
                    </div>
                </div>

                <!-- NAVIGATION -->
                <nav class="px-2.5 space-y-1 mt-1">
                    <div class="px-2 pb-1 text-[9px] font-black uppercase tracking-widest text-slate-400">Main Menu</div>

                    <a href="{{ route('student.dashboard') }}" 
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/dashboard') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-gauge-high w-4 text-center text-xs"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('student.updates') }}" 
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/updates*') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-bullhorn w-4 text-center text-xs text-amber-400"></i>
                        <span>Noticeboard</span>
                    </a>

                    <a href="{{ route('student.results') }}" 
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/results') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-graduation-cap w-4 text-center text-xs"></i>
                        <span>My Results</span>
                    </a>

                    <a href="{{ route('student.continuous_assessment') }}"
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/continuous-assessment*') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-clipboard-check w-4 text-center text-xs"></i>
                        <span>Class Activities</span>
                    </a>

                    <a href="{{ route('student.fees') }}" 
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/fees') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-wallet w-4 text-center text-xs"></i>
                        <span>Fees & Finance</span>
                    </a>

                    <a href="{{ route('student.ai_chat') }}" 
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/ai-chat*') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-robot w-4 text-center text-xs text-sky-400"></i>
                            <span>AI Support</span>
                        </div>
                        <span class="px-1.5 py-0.5 text-[9px] font-black rounded bg-sky-500 text-white">AI</span>
                    </a>

                    <div class="pt-3 px-2 pb-1 text-[9px] font-black uppercase tracking-widest text-slate-400">Settings</div>

                    <a href="{{ route('student.change_password') }}" 
                       class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Request::is('student/change-password') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-shield-halved w-4 text-center text-xs"></i>
                        <span>Security</span>
                    </a>
                </nav>
            </div>

            <div class="p-3 border-t border-slate-800 bg-slate-950">
                <a href="#" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="flex items-center justify-center space-x-2 w-full py-2 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all">
                    <i class="fa-solid fa-power-off text-xs"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
            
            <!-- TOP NAVBAR -->
            <header class="sticky top-0 z-30 h-14 bg-white dark:bg-slate-900 border-b border-slate-300 dark:border-slate-800 px-4 sm:px-6 flex items-center justify-between transition-colors shadow-xs">
                
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-1.5 rounded-lg text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i class="fa-solid fa-bars-staggered text-sm"></i>
                    </button>
                    
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Welcome, {{ Auth::guard('student')->user()->name }}</h2>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- THEME TOGGLER -->
                    <button @click="toggleTheme()" 
                            type="button"
                            title="Toggle Light/Dark Mode"
                            class="p-2 rounded-lg text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 font-bold">
                        <template x-if="darkMode">
                            <i class="fa-solid fa-sun text-amber-400 text-xs block"></i>
                        </template>
                        <template x-if="!darkMode">
                            <i class="fa-solid fa-moon text-slate-900 text-xs block"></i>
                        </template>
                    </button>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-800"></div>

                    <!-- USER MENU -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.outside="open = false"
                                class="flex items-center space-x-2 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-300">
                            <img src="{{ Auth::guard('student')->user()->gender == 'Female' ? asset('adminlte/dist/img/avatar3.png') : asset('adminlte/dist/img/avatar5.png') }}" 
                                 class="w-7 h-7 rounded-md object-cover ring-2 ring-slate-900 dark:ring-emerald-500" alt="Avatar">
                            <span class="text-xs font-black text-slate-900 dark:text-slate-100 hidden md:inline-block">{{ Auth::guard('student')->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-700 dark:text-slate-400 hidden md:inline-block"></i>
                        </button>

                        <div x-show="open" 
                             x-cloak
                             class="absolute right-0 mt-2 w-48 rounded-lg bg-white dark:bg-slate-900 shadow-xl border border-slate-300 dark:border-slate-800 py-1.5 z-50">
                            
                            <div class="px-3 py-1.5 border-b border-slate-200 dark:border-slate-800">
                                <p class="text-xs font-black text-slate-900 dark:text-slate-100 truncate">{{ Auth::guard('student')->user()->name }}</p>
                                <p class="text-[10px] font-bold text-slate-700 dark:text-emerald-400">Student Account</p>
                            </div>

                            <a href="{{ route('student.change_password') }}" class="flex items-center space-x-2 px-3 py-2 text-xs font-bold text-slate-900 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-lock text-slate-700 w-3.5"></i>
                                <span>Security Settings</span>
                            </a>

                            <div class="border-t border-slate-200 dark:border-slate-800 my-1"></div>

                            <a href="#" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="flex items-center space-x-2 px-3 py-2 text-xs font-bold text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                <i class="fa-solid fa-power-off w-3.5"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-4 sm:p-6 space-y-4">
                @hasSection('header')
                    <div class="mb-4">
                        @yield('header')
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="py-3 px-6 bg-white dark:bg-slate-900 border-t border-slate-300 dark:border-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <strong>Copyright &copy; {{ date('Y') }} {{ config('app.name', 'SMS') }}</strong>. All rights reserved.
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span class="font-extrabold text-slate-900 dark:text-slate-200 text-[10px]">Student Portal v2.6</span>
                </div>
            </footer>
        </div>
    </div>

    <form id="logout-form" action="{{ route('student.logout') }}" method="POST" class="hidden">
        @csrf
    </form>

</body>
</html>
