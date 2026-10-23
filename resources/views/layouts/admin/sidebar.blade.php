<!-- resources/views/layouts/admin/sidebar.blade.php -->
<aside class="flex flex-col h-screen w-64 bg-slate-900 dark:bg-slate-950 border-r border-slate-800 dark:border-slate-800/80 transition-transform duration-300 flex-shrink-0 z-20"
       x-show="sidebarOpen"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full">
    
    <!-- Brand Header -->
    <div class="flex flex-col items-center justify-center p-5 bg-slate-950 border-b border-slate-800">
        <h3 class="text-indigo-400 font-extrabold uppercase tracking-widest text-xs mb-2.5">
            {{ env('SCHOOL_ACRONYM', 'SCHOOL PORTAL') }}
        </h3>
        @if(!empty(env('SCHOOL_LOGO_PATH')))
            <img src="{{ asset(env('SCHOOL_LOGO_PATH')) }}" alt="Logo" class="w-12 h-12 bg-white rounded-full border-2 border-indigo-500 object-cover shadow-md hover:scale-105 transition-transform">
        @else
            <div class="w-11 h-11 bg-indigo-600 rounded-full flex items-center justify-center border-2 border-indigo-400 shadow-md text-white">
                <i class="fa-solid fa-graduation-cap text-lg"></i>
            </div>
        @endif
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4 px-3 space-y-1">
        <div class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 px-3">MAIN NAVIGATION</div>

        {{-- Dashboards --}}
        @can('view-admin-dashboard')
        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg {{ Request::is('dashboard*') ? 'bg-indigo-500/20 text-indigo-400 border-l-2 border-indigo-500' : 'text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white' }} transition-colors">
            <i class="fa-solid fa-table-cells-large text-indigo-400 w-5 text-center mr-2.5 text-sm"></i>
            <span class="font-semibold">Admin Dashboard</span>
        </a>
        @endcan

        @can('view-teacher-dashboard')
        <a href="{{ route('teacher.dashboard') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg {{ Request::is('teacher/dashboard*') ? 'bg-indigo-500/20 text-indigo-400 border-l-2 border-indigo-500' : 'text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white' }} transition-colors">
            <i class="fa-solid fa-tv text-indigo-400 w-5 text-center mr-2.5 text-sm"></i>
            <span class="font-semibold">Teacher Portal</span>
        </a>
        @endcan

        {{-- System Administration --}}
        @canany(['manage-users', 'audit-logs'])
        <div x-data="{ open: {{ Request::is('administration*') || Request::is('users*') || Request::is('roles*') || Request::is('audit-logs*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-shield-halved text-rose-500 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Administration</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('manage-users')
                <a href="{{ route('roles.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('administration/roles*') || Request::is('roles*') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-key mr-2 text-[10px] text-slate-400"></i> Roles & Permissions
                </a>
                <a href="{{ route('users.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('users/create') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-user-plus mr-2 text-[10px] text-slate-400"></i> Add New Staff
                </a>
                <a href="{{ route('users.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('users') && !Request::is('users/create') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-users mr-2 text-[10px] text-slate-400"></i> Staff Directory
                </a>
                @endcan
                @can('audit-logs')
                <a href="{{ route('audit.logs') }}" class="block px-2.5 py-1.5 text-xs font-medium text-amber-400 hover:text-amber-300 rounded-md {{ Request::is('audit-logs*') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-[10px]"></i> System Audit Logs
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- Payroll --}}
        @can('manage-payroll')
        <div x-data="{ open: {{ Request::is('payroll*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-calculator text-amber-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Payroll</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('payroll.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('payroll*') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Payroll
                </a>
            </div>
        </div>
        @endcan

        {{-- Students --}}
        @if(auth()->user()?->can('student_management') || auth()->user()?->can('promote-students') || auth()->user()?->hasRole('teacher') || auth()->user()?->hasRole('receptionist'))
        <div x-data="{ open: {{ Request::is('students*') || Request::is('receptionist/students*') || Request::is('teacher/my-class*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-user-graduate text-purple-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Students</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('student_management')
                <a href="{{ route('students.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('students/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Add Student</a>
                <a href="{{ route('students.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('students/manage') || Request::is('students') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Students</a>
                <a href="{{ route('students.enrollment_stats') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('students/stats*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Enrollment Stats</a>
                @endcan

                @can('promote-students')
                <a href="{{ route('students.promote') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('students/promote*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Promote Students</a>
                @endcan

                @role('receptionist')
                <a href="{{ route('receptionist.students.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('receptionist/students*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Student Directory</a>
                @endrole

                @role('teacher')
                <a href="{{ route('teacher.my_class') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('teacher/my-class*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> My Class Roster</a>
                @endrole
            </div>
        </div>
        @endif

        {{-- Admissions --}}
        @can('admissions')
        <div x-data="{ open: {{ Request::is('admissions*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-id-card text-emerald-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Admissions</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('admissions.manage') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('admissions') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-list-check mr-2 text-[10px] text-slate-400"></i> Manage Applications
                </a>
                <a href="{{ route('admissions.payment-proofs') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('admissions/payment-proofs*') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-receipt mr-2 text-[10px] text-slate-400"></i> Payment Proof Approvals
                </a>
            </div>
        </div>
        @endcan

        {{-- Classes --}}
        @can('manage-classes')
        <div x-data="{ open: {{ Request::is('classes*') || Request::is('receptionist/classes*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-chalkboard-user text-rose-500 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Classes</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('classes.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('classes') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Classes</a>
                <a href="{{ route('classes.assign') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('classes/assign-subjects*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Assign Subjects</a>
            </div>
        </div>
        @endcan


        {{-- Timetable --}}
        @can('manage-timetables')
        <div x-data="{ open: {{ Request::is('admin/timetable*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-calendar-days text-green-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Timetable</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('timetable.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('admin/timetable') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-table-list mr-2 text-[10px] text-slate-400"></i> Class Timetables</a>
                <a href="{{ route('timetable.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('admin/timetable/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-plus mr-2 text-[10px] text-slate-400"></i> Add New Slot</a>
            </div>
        </div>
        @endcan

        {{-- Subjects --}}
        @can('view-subjects')
        <div x-data="{ open: {{ Request::is('subjects*') || Request::is('teacher/my-subjects*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-book-bookmark text-indigo-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Subjects</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('manage-subjects')
                <a href="{{ route('subjects.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('subjects*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Subjects</a>
                @endcan

                @can('view-assigned-subjects')
                <a href="{{ route('teacher.subjects') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('teacher/my-subjects*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> My Assigned Subjects</a>
                @endcan
            </div>
        </div>
        @endcan

        {{-- Semesters / Terms --}}
        @can('term_management')
        <div x-data="{ open: {{ Request::is('terms*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-layer-group text-slate-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Semesters/Terms</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('terms.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('terms*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Terms</a>
            </div>
        </div>
        @endcan

        {{-- Announcements --}}
        @can('announcement_management')
        <div x-data="{ open: {{ Request::is('announcements*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-bullhorn text-sky-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Announcements</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('announcements.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('announcements/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-circle-plus mr-2 text-[10px] text-slate-400"></i> Create Announcement</a>
                <a href="{{ route('announcements.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('announcements') && !Request::is('announcements/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-list mr-2 text-[10px] text-slate-400"></i> Manage Announcements</a>
            </div>
        </div>
        @endcan

        {{-- Newsletter --}}
        @can('newsletter_management')
        <div x-data="{ open: {{ Request::is('newsletters*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-newspaper text-purple-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Newsletter</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('newsletters.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('newsletters/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-paper-plane mr-2 text-[10px] text-slate-400"></i> Compose Newsletter</a>
                <a href="{{ route('newsletters.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('newsletters') && !Request::is('newsletters/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-envelope mr-2 text-[10px] text-slate-400"></i> View Newsletters</a>
            </div>
        </div>
        @endcan

        {{-- Attendance --}}
        @can('attendance_management')
        <div x-data="{ open: {{ Request::is('attendance*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-clipboard-check text-emerald-500 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Attendance</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="#" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Mark Attendance</a>
                <a href="#" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> View Attendance</a>
            </div>
        </div>
        @endcan

        {{-- Exams --}}
        @if(auth()->user()?->can('exams_management') || auth()->user()?->hasRole('teacher'))
        <div x-data="{ open: {{ Request::is('exams*') || Request::is('marks*') || Request::is('teacher/exams*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-hourglass-half text-pink-500 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Exams</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('exams_management')
                <a href="{{ route('exams.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('exams') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Exam Schedule</a>
                <a href="{{ route('results.access') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('results/*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-unlock-keyhole mr-2 text-[10px]"></i> Publish Results & Exemptions</a>
                <a href="{{ route('exams.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-emerald-400 hover:text-emerald-300 rounded-md {{ Request::is('exams/*/verify') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-user-shield mr-2 text-[10px]"></i> Biometric Gatekeeper</a>
                @endcan

                @role('teacher')
                <a href="{{ route('teacher.exams.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('teacher/exams*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Teacher Exam Portal</a>
                @endrole
            </div>
        </div>
        @endif

        {{-- Continuous Assessment --}}
        @if(auth()->user()?->can('activities_management') || auth()->user()?->hasRole('teacher'))
        <div x-data="{ open: {{ Request::is('activities*') || Request::is('teacher/activities*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-list-check text-emerald-500 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Continuous Assessment</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('activities_management')
                <a href="{{ route('activities.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('activities') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> All Logged Activities</a>
                <a href="{{ route('activities.analytics') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('activities/analytics') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-chart-column mr-2 text-[10px] text-slate-400"></i> Analytics & AI Insights</a>
                @endcan

                @can('edit-activities')
                <a href="{{ route('teacher.activities.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('teacher/activities*') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> My Class Activities</a>
                @endcan
            </div>
        </div>
        @endif

        {{-- Fees --}}
        @if(auth()->user()?->can('manage-fees') || auth()->user()?->hasRole('receptionist'))
        <div x-data="{ open: {{ Request::is('fees*') || Request::is('receptionist/payments*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-money-bill-wave text-emerald-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Fees</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @can('manage-fees')
                <a href="{{ route('fees.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('fees/payment') || Request::is('fees/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Make Payment</a>
                <a href="{{ route('fees.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('fees/history') || Request::is('fees') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> View Payments</a>
                <a href="{{ route('fees.structure') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('fees/structure') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-gears mr-2 text-[10px] text-slate-400"></i> Fee Setup</a>
                <a href="{{ route('fees.report') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('fees/balance-report') || Request::is('fees/report') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Payment Report</a>
                @endcan

                @role('receptionist')
                <a href="{{ route('receptionist.payments.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('receptionist/payments/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-solid fa-plus mr-2 text-[10px] text-slate-400"></i> Record Receipt</a>
                <a href="{{ route('receptionist.payments.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('receptionist/payments') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-file-lines mr-2 text-[10px] text-slate-400"></i> Payment History</a>
                @endrole
            </div>
        </div>
        @endif

        {{-- Expenses --}}
        @can('manage-expenses')
        <div x-data="{ open: {{ Request::is('expenses*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-credit-card text-rose-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Expenses</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                <a href="{{ route('expenses.create') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('expenses/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Add Expense</a>
                <a href="{{ route('expenses.index') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('expenses') && !Request::is('expenses/create') ? 'text-white bg-slate-800' : '' }}"><i class="fa-regular fa-circle mr-2 text-[8px] text-slate-400"></i> Manage Expenses</a>
            </div>
        </div>
        @endcan

        {{-- Inventory --}}
        @can('manage-inventory')
        <a href="{{ route('inventory.index') }}" class="flex items-center px-3 py-2.5 text-xs font-medium rounded-lg {{ Request::is('inventory*') ? 'bg-indigo-500/20 text-indigo-400 border-l-2 border-indigo-500' : 'text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white' }} transition-colors">
            <i class="fa-solid fa-box-archive text-amber-400 w-5 text-center mr-2.5 text-sm"></i>
            <span class="font-semibold">Inventory</span>
        </a>
        @endcan

        {{-- Settings --}}
        <div x-data="{ open: {{ Request::is('settings*') || Request::is('teacher/profile*') || Request::is('receptionist/profile*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-medium rounded-lg text-slate-200 hover:bg-slate-800 dark:hover:bg-slate-900/80 hover:text-white transition-colors">
                <div class="flex items-center">
                    <i class="fa-solid fa-gear text-slate-400 w-5 text-center mr-2.5 text-sm"></i>
                    <span class="font-semibold">Settings</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transform transition-transform" :class="{'rotate-180': open}"></i>
            </button>
            <div x-show="open" x-collapse class="pl-8 pr-2 py-1 space-y-1 bg-slate-950/70 rounded-lg mt-1 border border-slate-800/50">
                @hasanyrole(['admin', 'super-admin'])
                <a href="{{ route('admin.profile') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('settings/profile') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-user mr-2 text-[10px] text-slate-400"></i> Edit Profile
                </a>
                @endhasanyrole

                @unlessrole(['teacher', 'receptionist', 'admin', 'super-admin'])
                <a href="{{ route('admin.profile') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('settings/profile') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-user mr-2 text-[10px] text-slate-400"></i> Edit Profile
                </a>
                @endunlessrole

                <a href="{{ route('admin.change_password') }}" class="block px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white rounded-md {{ Request::is('settings/change-password') ? 'text-white bg-slate-800' : '' }}">
                    <i class="fa-solid fa-lock text-cyan-400 mr-2 text-[10px]"></i> Change Password
                </a>
            </div>
        </div>

    </nav>

    <!-- Logout Footer Section -->
    <div class="p-4 border-t border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center w-full px-3 py-2.5 text-xs font-semibold text-rose-400 rounded-lg hover:bg-rose-500/10 hover:text-rose-300 transition-colors">
                <i class="fa-solid fa-right-from-bracket w-5 text-center mr-2.5 text-sm"></i>
                Logout
            </button>
        </form>
    </div>
</aside>
