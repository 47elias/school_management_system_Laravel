{{-- resources/views/students/manage.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Manage Students | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Student Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Registry & Records Database</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('students.create') ? route('students.create') : url('/students/create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                <i class="fa-solid fa-user-plus mr-2 text-xs"></i> Register New Student
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            search: '', 
            showProfileModal: false, 
            modalStudentName: '',
            modalStudentId: null,
            profileContent: '' 
         }" 
         class="space-y-6">

        {{-- Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" x-on:click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Student Directory Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-users text-indigo-500 mr-2.5"></i> Student Directory
                </h3>

                {{-- Live Filter Search --}}
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="search" placeholder="Filter records..." 
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gender</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Level</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($students as $student)
                        <tr x-show="!search || '{{ strtolower($student->student_number . ' ' . $student->surname . ' ' . $student->name . ' ' . $student->grade) }}'.includes(search.toLowerCase())"
                            class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            
                            {{-- ID Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-600">
                                    {{ $student->student_number }}
                                </span>
                            </td>

                            {{-- Student Name (Clear, bold text) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-slate-900 dark:text-white tracking-wide">
                                    {{ $student->surname }}, {{ $student->name }}
                                </span>
                            </td>

                            {{-- Gender --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300 font-medium">
                                {{ $student->gender }}
                            </td>

                            {{-- Grade --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                    {{ $student->grade }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->status == 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">ACTIVE</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">INACTIVE</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end space-x-1">
                                    {{-- Quick Modal Preview (profile_data.blade.php) --}}
                                    <button type="button" 
                                            x-on:click="
                                                modalStudentName = '{{ $student->surname }}, {{ $student->name }}';
                                                profileContent = '<div class=\'flex justify-center p-6\'><i class=\'fa-solid fa-circle-notch fa-spin text-2xl text-indigo-500\'></i></div>';
                                                showProfileModal = true;
                                                fetch('/students/{{ $student->id }}/profile-data')
                                                    .then(res => res.text())
                                                    .then(html => profileContent = html)
                                                    .catch(() => profileContent = '<p class=\'text-xs text-rose-500 p-4\'>Error loading student profile data.</p>');
                                            "
                                            class="p-2 rounded-lg text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors" 
                                            title="Quick Preview Modal">
                                        <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                                    </button>

                                    {{-- Full Profile Page (show.blade.php) --}}
                                    <a href="{{ Route::has('students.show') ? route('students.show', $student->id) : url('/students/' . $student->id) }}" 
                                       class="p-2 rounded-lg text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition-colors" 
                                       title="View Full Profile Page">
                                        <i class="fa-solid fa-id-card text-sm"></i>
                                    </a>

                                    {{-- Edit Record --}}
                                    <a href="{{ Route::has('students.edit') ? route('students.edit', $student->id) : url('/students/' . $student->id . '/edit') }}" 
                                       class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors" 
                                       title="Edit Record">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>

                                    {{-- Delete Record --}}
                                    <form action="{{ Route::has('students.destroy') ? route('students.destroy', $student->id) : url('/students/' . $student->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to permanently delete student \'{{ $student->name }}\'?')"
                                                class="p-2 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" 
                                                title="Delete Student">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No student records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- QUICK PROFILE MODAL (Fetches profile_data.blade.php) --}}
        <div x-show="showProfileModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-on:click="showProfileModal = false" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-slate-900 rounded-2xl shadow-xl border border-slate-800">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-sm font-bold text-white flex items-center">
                            <i class="fa-solid fa-id-card text-indigo-400 mr-2"></i> Quick Profile: <span x-text="modalStudentName" class="ml-1 text-slate-200"></span>
                        </h3>
                        <button type="button" x-on:click="showProfileModal = false" class="text-slate-400 hover:text-slate-200">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                    <div class="mt-4" x-html="profileContent"></div>
                </div>
            </div>
        </div>

    </div>
@endsection