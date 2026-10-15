{{-- resources/views/classes/students.blade.php --}}
@extends('layouts.admin.app')

@section('title', $class->class_name . ' Roster | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $class->class_name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Class Roster & Student Records (Code: {{ $class->class_code }})</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('classes.index') ? route('classes.index') : url('/classes') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Classes
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ search: '' }" class="space-y-6">

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-users text-indigo-500 mr-2.5"></i> Registered Students
                </h3>

                {{-- Filter Input --}}
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="search" placeholder="Filter roster..." 
                           class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Surname</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">First Name</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gender</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($class->students as $student)
                        <tr x-show="!search || '{{ strtolower($student->student_number . ' ' . $student->surname . ' ' . $student->name) }}'.includes(search.toLowerCase())"
                            class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-600">
                                    {{ $student->student_number }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-xs font-bold text-slate-900 dark:text-white tracking-wide uppercase">{{ $student->surname }}</strong>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700 dark:text-slate-300 font-medium">
                                {{ $student->name }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $gender = strtolower($student->gender ?? 'n/a'); @endphp
                                @if($gender == 'male')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50">MALE</span>
                                @elseif($gender == 'female')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 dark:bg-pink-950/50 text-pink-600 dark:text-pink-400 border border-pink-200 dark:border-pink-800/50">FEMALE</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">N/A</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <a href="{{ Route::has('students.show') ? route('students.show', $student->id) : url('/students/' . $student->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 transition-colors">
                                    <i class="fa-solid fa-id-card mr-1.5"></i> View Profile
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <i class="fa-solid fa-user-slash text-xl"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 dark:text-slate-300">No Students Found</h4>
                                <p class="text-xs text-slate-400 mt-1">No students are assigned to this class yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection