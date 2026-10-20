@extends('layouts.admin.app')

@section('title', 'My Assigned Subjects')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-book-bookmark text-indigo-600 dark:text-indigo-400"></i><span>My Subjects</span></h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your subjects and the classes or streams assigned to you.</p>
        </div>
        <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
    </div>
@endsection

@section('content')
<section class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60">
        <div><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">My Subjects and Classes</h2><p class="mt-1 text-xs text-slate-500">{{ $assignments->count() }} assigned {{ \Illuminate\Support\Str::plural('subject', $assignments->count()) }}</p></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-left">
            <thead class="bg-slate-50/80 dark:bg-slate-900/50 text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400"><tr><th class="px-5 py-3">Subject</th><th class="px-5 py-3">Code</th><th class="px-5 py-3">Assigned class</th><th class="px-5 py-3">Academic year</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                        <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400"><i class="fa-solid fa-book"></i></span><span class="font-bold text-xs text-slate-800 dark:text-slate-100">{{ $assignment->subject->subject_name ?? 'Subject unavailable' }}</span></div></td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $assignment->subject->subject_code ?? '—' }}</td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-lg bg-teal-50 dark:bg-teal-950/50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:text-teal-300">{{ $assignment->class_label }}</span></td>
                        <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-300">{{ $assignment->academic_year ?? '—' }}</td>
                        <td class="px-5 py-4 text-right"><div class="inline-flex gap-2"><a href="{{ route('teacher.subjects.marks', ['subject_id' => $assignment->subject_id, 'grade' => $assignment->grade ?? $assignment->schoolClass?->gradeLevel?->name, 'class_id' => $assignment->class_id]) }}" class="rounded-lg bg-amber-50 dark:bg-amber-950/50 px-3 py-2 text-[11px] font-bold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50"><i class="fa-solid fa-chart-column mr-1"></i>Marks</a><a href="{{ route('teacher.activities.index') }}" class="rounded-lg bg-indigo-50 dark:bg-indigo-950/50 px-3 py-2 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50"><i class="fa-solid fa-list-check mr-1"></i>Activities</a><a href="{{ route('teacher.exams.index') }}" class="rounded-lg bg-emerald-50 dark:bg-emerald-950/50 px-3 py-2 text-[11px] font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50"><i class="fa-solid fa-pen-to-square mr-1"></i>Exams</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-14 text-center"><span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400"><i class="fa-solid fa-book-open"></i></span><p class="font-bold text-sm text-slate-700 dark:text-slate-200">No subjects assigned yet</p><p class="mt-1 text-xs text-slate-500">Your teaching allocations will appear here when assigned.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
