@extends('layouts.admin.app')

@section('title', 'Classwork and Homework')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div><h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-list-check text-indigo-600 dark:text-indigo-400"></i><span>Classwork and Homework</span></h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add classwork, homework, quizzes, and other tasks for your classes.</p></div>
        <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
    </div>
@endsection

@section('content')
<div x-data="{ modalOpen: false, assignmentId: '', subjectName: '', className: '' }" class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-semibold text-emerald-800 dark:text-emerald-200"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-sm font-semibold text-rose-800 dark:text-rose-200"><i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-sm text-rose-800 dark:text-rose-200">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($assignments as $item)
            <article class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
                <div class="bg-gradient-to-r from-indigo-700 to-violet-700 px-5 py-5 text-white">
                    <div class="flex items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-lg font-black">{{ strtoupper(substr($item->subject->subject_name ?? 'S', 0, 1)) }}</span><div class="min-w-0"><h2 class="truncate font-bold">{{ $item->subject->subject_name ?? 'Subject' }}</h2><p class="mt-1 truncate text-xs text-indigo-100"><i class="fa-solid fa-school mr-1"></i>{{ $item->class_label }}</p></div></div>
                </div>
                <div class="flex items-center justify-between px-5 py-4"><span class="text-xs text-slate-500 dark:text-slate-400">Activities logged</span><span class="rounded-full bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 text-xs font-black text-indigo-700 dark:text-indigo-300">{{ $item->activities_count }}</span></div>
                <div class="flex gap-2 px-5 pb-5">
                    <button type="button" data-assignment-id="{{ $item->id }}" data-subject="{{ $item->subject->subject_name ?? 'Subject' }}" data-class="{{ $item->class_label }}" @click="modalOpen = true; assignmentId = $el.dataset.assignmentId; subjectName = $el.dataset.subject; className = $el.dataset.class" class="flex-1 rounded-xl bg-indigo-600 px-3 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-plus mr-1.5"></i>Add Activity</button>
                    <a href="{{ route('teacher.activities.history', $item->id) }}" class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2.5 text-center text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fa-solid fa-clock-rotate-left mr-1.5"></i>View Marks</a>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/70 dark:bg-slate-800/50 px-6 py-14 text-center"><span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400"><i class="fa-solid fa-circle-info"></i></span><h2 class="font-bold text-slate-800 dark:text-slate-100">No teaching allocations found</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">You need a subject and class allocation before recording activities.</p></div>
        @endforelse
    </div>

    <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" @keydown.escape.window="modalOpen = false" @click.self="modalOpen = false">
        <div x-transition class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl">
            <form action="{{ route('teacher.activities.store') }}" method="POST">
                @csrf
                <input type="hidden" name="subject_assignment_id" :value="assignmentId">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-slate-700 px-6 py-5"><div><h2 class="font-bold text-slate-800 dark:text-slate-100">Add classwork or homework</h2><p class="mt-1 text-xs text-slate-500"><span x-text="subjectName"></span> <span class="mx-1">·</span> <span x-text="className"></span></p></div><button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
                    <label class="sm:col-span-2 text-xs font-bold text-slate-600 dark:text-slate-300">Activity title<input name="title" value="{{ old('title') }}" required maxlength="255" placeholder="e.g. Chapter 4 Homework" class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100"></label>
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Activity type<select name="type" required class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100"><option value="classwork">Classwork</option><option value="homework">Homework</option><option value="quiz">Quiz</option><option value="participation">Participation</option><option value="practical">Practical</option><option value="project">Project</option><option value="other">Other</option></select></label>
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Date<input type="date" name="activity_date" value="{{ old('activity_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100"></label>
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Maximum score<input type="number" name="max_score" value="{{ old('max_score', 100) }}" min="1" max="1000" required class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100"></label>
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Weight <span class="font-normal text-slate-400">(optional)</span><input type="number" step="0.1" name="weight" value="{{ old('weight', '1.0') }}" min="0.1" max="10" class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100"></label>
                    <label class="sm:col-span-2 text-xs font-bold text-slate-600 dark:text-slate-300">Notes <span class="font-normal text-slate-400">(optional)</span><textarea name="notes" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-sm font-normal text-slate-800 dark:text-slate-100">{{ old('notes') }}</textarea></label>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-6 py-4"><button type="button" @click="modalOpen = false" class="rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">Cancel</button><button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-arrow-right mr-1.5"></i>Create and Enter Marks</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
