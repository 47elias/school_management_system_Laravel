<!-- resources/views/layouts/admin/footer.blade.php -->
<footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 mt-auto shrink-0 transition-colors">
    
    <!-- Left: Copyright Notice & Link -->
    <div class="flex items-center mb-2 md:mb-0">
        <span class="font-medium text-slate-700 dark:text-slate-300">Copyright &copy; {{ date('Y') }}</span>
        <a href="https://www.elias.co.zw" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="ml-1 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition-colors">
            {{ config('app.name', 'School Portal') }}
        </a>. 
        <span class="ml-1 hidden sm:inline text-slate-500 dark:text-slate-400">All rights reserved.</span>
    </div>

    <!-- Right: Build & Version Information -->
    <div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400">
        <i class="fa-solid fa-code-branch text-indigo-500 dark:text-indigo-400 text-xs"></i>
        <span class="font-medium">
            Mukahlera Build 
            <span class="font-extrabold text-indigo-600 dark:text-indigo-400 ml-1">v{{ env('PORTAL_VERSION', '1.0.0') }}</span>
        </span>
    </div>

</footer>