@extends('layouts.admin.app')

@section('title', $activity->title . ' | Continuous Assessment')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4 w-full">
    <div><h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $activity->title }}</h1><p class="mt-1 text-sm text-slate-500">{{ $activity->subject->subject_name ?? 'Subject' }} · {{ $activity->subjectAssignment->class_label }} · {{ $activity->formatted_date }}</p></div>
    <a href="{{ route('activities.index') }}" class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-arrow-left mr-1.5"></i>All Activities</a>
</div>
@endsection

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['Type', $activity->type_label], ['Term', $activity->term->term_name ?? 'N/A'], ['Maximum score', $activity->max_score], ['Selected group average', $visibleAverage . '%']] as [$label, $value])
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p><p class="mt-2 font-black text-slate-800 dark:text-slate-100">{{ $value }}</p></div>
        @endforeach
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-4">
        <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Grade / Form<select name="grade_level_id" class="mt-1 block min-w-40 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2"><option value="">All levels</option>@foreach($gradeLevels as $grade)<option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>@endforeach</select></label>
        <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Stream<select name="class_id" class="mt-1 block min-w-40 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2"><option value="">All streams</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->class_name }}</option>@endforeach</select></label>
        <button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white">Apply filter</button><a href="{{ route('activities.show', $activity->id) }}" class="px-3 py-2.5 text-xs font-bold text-slate-500">Clear</a>
    </form>
    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        <div class="border-b border-slate-200 dark:border-slate-700 px-5 py-4"><h2 class="font-bold text-slate-800 dark:text-slate-100">Student marks by stream</h2><p class="mt-1 text-xs text-slate-500">{{ $marks->count() }} recorded marks. Marks are grouped by each student's actual stream.</p></div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-xs"><thead class="bg-slate-50 dark:bg-slate-900/60 text-[10px] uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Form / Stream</th><th class="px-5 py-3 text-right">Score</th><th class="px-5 py-3 text-right">Percent</th><th class="px-5 py-3">Comment</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($marks as $mark)<tr><td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $mark->student->surname }}, {{ $mark->student->name }} <span class="block mt-1 font-mono text-[10px] text-slate-500">{{ $mark->student->student_number }}</span></td><td class="px-5 py-3">{{ $mark->student->schoolClass->class_name ?? 'Unassigned' }}</td><td class="px-5 py-3 text-right font-bold">{{ $mark->score }} / {{ $activity->max_score }}</td><td class="px-5 py-3 text-right font-black {{ $mark->percent >= 50 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $mark->percent }}%</td><td class="px-5 py-3 text-slate-500">{{ $mark->comment ?: '—' }}</td></tr>
            @empty<tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No marks have been recorded for this activity.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
