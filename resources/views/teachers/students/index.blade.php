@extends('layouts.admin.app')

@section('title', 'My Students')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-users text-indigo-600 dark:text-indigo-400"></i>
                <span>My Students</span>
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Students in the classes assigned to you.</p>
        </div>
        <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ search: '' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 p-5 shadow-xs flex items-center gap-4">
            <span class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl"><i class="fa-solid fa-school"></i></span>
            <div><p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Your Class</p><p class="mt-1 font-bold text-slate-800 dark:text-slate-100">{{ $myClass->class_name ?? 'More than one class' }}</p></div>
        </div>
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 p-5 shadow-xs flex items-center gap-4">
            <span class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl"><i class="fa-solid fa-user-graduate"></i></span>
            <div><p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Active Students</p><p class="mt-1 text-2xl font-black text-slate-800 dark:text-slate-100">{{ $students->count() }}</p></div>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60">
            <div><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Class List</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $students->count() }} active {{ \Illuminate\Support\Str::plural('student', $students->count()) }}</p></div>
            <label class="relative block w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="search" x-model="search" placeholder="Search name or student ID" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 py-2.5 pl-9 pr-3 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </label>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-900/50 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Student ID</th><th class="px-5 py-3">Class</th><th class="px-5 py-3">Gender</th><th class="px-5 py-3">Guardian contact</th><th class="px-5 py-3 text-right">Profile</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($students as $student)
                        @php $studentNumber = $student->student_number ?? $student->id; $guardianPhone = $student->parent_contact ?? $student->guardian_phone ?? $student->phone; @endphp
                        <tr x-show="!search || $el.dataset.search.includes(search.toLowerCase())" data-search="{{ strtolower($student->name.' '.$student->surname.' '.$studentNumber) }}" class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                            <td class="px-5 py-3.5"><div class="flex items-center gap-3"><img src="https://ui-avatars.com/api/?name={{ urlencode($student->name.' '.$student->surname) }}&background=4f46e5&color=fff&size=80" class="w-9 h-9 rounded-full" alt=""><div><p class="font-bold text-xs text-slate-800 dark:text-slate-100">{{ $student->name }} {{ $student->surname }}</p><p class="text-[10px] text-slate-400">{{ ucfirst($student->status ?? 'active') }}</p></div></div></td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $studentNumber }}</td>
                            <td class="px-5 py-3.5 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $student->schoolClass->class_name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $student->gender ? ucfirst($student->gender) : '—' }}</td>
                            <td class="px-5 py-3.5 text-xs">@if($guardianPhone)<a href="tel:{{ $guardianPhone }}" class="text-indigo-600 dark:text-indigo-400 hover:underline"><i class="fa-solid fa-phone mr-1"></i>{{ $guardianPhone }}</a>@else<span class="text-slate-400">Not provided</span>@endif</td>
                            <td class="px-5 py-3.5 text-right"><a href="{{ route('teacher.students.show', $student->id) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/60"><i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i>View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center"><span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400"><i class="fa-solid fa-user-group"></i></span><p class="font-bold text-sm text-slate-700 dark:text-slate-200">No students in your assigned classes</p><p class="mt-1 text-xs text-slate-500">Students will appear here when they are enrolled in one of your classes.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
