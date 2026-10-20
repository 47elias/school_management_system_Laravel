<!-- resources/views/layouts/admin/app.blade.php -->
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'School Portal')) | {{ request()->is('teacher*') ? 'Teacher Portal' : 'Admin' }}</title>

    <!-- Tailwind CSS with Dark Mode Class Strategy -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    
    <!-- Modern Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Pro/Free Modern Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Alpine.js Plugins & Core Engine -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Immediate Theme Application Script (Prevents White Screen Flash on Dark Mode) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'system';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Give teacher pages a softer, warmer light background while keeping
           the shared admin layout and dark theme intact. */
        html:not(.dark) .teacher-portal > div.flex.h-screen { background-color: #f1efe7; }
        html:not(.dark) .teacher-portal main { background-color: #f1efe7 !important; }
        html:not(.dark) .teacher-portal main .bg-white { background-color: #faf9f4; }
        html:not(.dark) .teacher-portal main .bg-slate-50 { background-color: #f4f2eb; }
        html:not(.dark) .teacher-portal main [class~="bg-white/70"] { background-color: rgb(250 249 244 / 0.8); }
        
        /* Smooth Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #475569; }
    </style>

    @stack('styles')
</head>
<body class="{{ request()->is('teacher*') ? 'teacher-portal' : '' }} bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased overflow-hidden h-full" 
      x-data="{ 
          sidebarOpen: true, 
          isFullscreen: false,
          theme: localStorage.getItem('theme') || 'system',
          
          setTheme(mode) {
              this.theme = mode;
              localStorage.setItem('theme', mode);
              this.applyTheme();
          },

          applyTheme() {
              if (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          },

          toggleFullscreen() {
              if (!document.fullscreenElement) {
                  document.documentElement.requestFullscreen().then(() => {
                      this.isFullscreen = true;
                  }).catch(err => {
                      console.error(`Fullscreen request failed: ${err.message}`);
                  });
              } else {
                  if (document.exitFullscreen) {
                      document.exitFullscreen().then(() => {
                          this.isFullscreen = false;
                      });
                  }
              }
          },

          init() {
              this.applyTheme();
              window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                  if (this.theme === 'system') this.applyTheme();
              });
              document.addEventListener('fullscreenchange', () => {
                  this.isFullscreen = !!document.fullscreenElement;
              });
          }
      }">
    
    <div class="flex h-screen w-full bg-slate-50 dark:bg-slate-950">
        
        <!-- Sidebar Navigation -->
        @include('layouts.admin.sidebar')

        <!-- Main Workspace Area -->
        <div class="flex flex-col flex-1 w-full overflow-hidden transition-all duration-300">
            
            <!-- Top Navigation Bar -->
            @include('layouts.admin.topbar')

            <!-- Main Page Content Slot -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 dark:bg-slate-900/50 p-4 sm:p-6">
                @hasSection('header')
                    <div class="mb-6 flex items-center justify-between">
                        @yield('header')
                    </div>
                @endif
                
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.admin.footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
