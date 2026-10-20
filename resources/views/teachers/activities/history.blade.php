@extends('layouts.admin.app')

@section('title', 'Activity Marks')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div><h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-indigo-600 dark:text-indigo-400"></i><span>Activity Marks</span></h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $assignment->subject->subject_name ?? 'Subject' }} <span class="mx-1">·</span> {{ $assignment->class_label ?? 'Class' }} @if($term)<span class="mx-1">·</span>{{ $term->term_name }}@endif</p></div>
        <a href="{{ route('teacher.activities.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fa-solid fa-arrow-left mr-2"></i> Activities</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-semibold text-emerald-800 dark:text-emerald-200"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-sm font-semibold text-rose-800 dark:text-rose-200"><i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}</div>@endif
    <div class="grid grid-cols-1 2xl:grid-cols-5 gap-6">
        <section class="2xl:col-span-3 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60"><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Classwork and Homework</h2><p class="mt-1 text-xs text-slate-500">{{ $activities->count() }} activities · {{ $students->count() }} students</p></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-900/50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Activity</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Date</th><th class="px-5 py-3 text-center">Scores</th><th class="px-5 py-3 text-center">Average</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30"><td class="px-5 py-3.5"><p class="font-bold text-xs text-slate-800 dark:text-slate-100">{{ $activity->title }}</p>@if($activity->notes)<p class="mt-1 max-w-xs text-[11px] text-slate-500">{{ $activity->notes }}</p>@endif</td><td class="px-5 py-3.5"><span class="rounded-full bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 text-[10px] font-bold text-indigo-700 dark:text-indigo-300">{{ $activity->type_label }}</span></td><td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $activity->formatted_date }}</td><td class="px-5 py-3.5 text-center text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $activity->marks_count }} / {{ $students->count() }}</td><td class="px-5 py-3.5 text-center"><span class="rounded-lg px-2.5 py-1 text-xs font-black {{ $activity->average_percent >= 50 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300' }}">{{ $activity->average_percent }}%</span></td><td class="px-5 py-3.5 text-right"><div class="inline-flex gap-1.5"><a href="{{ route('teacher.activities.record', $activity->id) }}" class="rounded-lg bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-2 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100" title="Edit scores"><i class="fa-solid fa-pen"></i></a><form action="{{ route('teacher.activities.destroy', $activity->id) }}" method="POST" onsubmit="return confirm('Delete this activity and all its scores?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-rose-50 dark:bg-rose-950/50 px-2.5 py-2 text-rose-700 dark:text-rose-300 hover:bg-rose-100" title="Delete"><i class="fa-solid fa-trash"></i></button></form></div></td></tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No activities have been logged for this term.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </section>
        <section class="2xl:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60"><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Student Averages</h2><p class="mt-1 text-xs text-slate-500">{{ $term->term_name ?? 'Current term' }}</p></div>
            <div class="max-h-[34rem] overflow-auto"><table class="w-full text-left"><thead class="sticky top-0 bg-slate-50 dark:bg-slate-900/80 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3 text-right">Average</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($students as $student)<tr><td class="px-5 py-3 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $student->surname }}, {{ $student->name }}</td><td class="px-5 py-3 text-right"><span class="rounded-lg px-2.5 py-1 text-xs font-black {{ $student->ca_average >= 50 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300' }}">{{ $student->ca_average }}%</span></td></tr>@empty<tr><td colspan="2" class="px-5 py-12 text-center text-sm text-slate-500">No active students in this class.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</div>
@endsection
