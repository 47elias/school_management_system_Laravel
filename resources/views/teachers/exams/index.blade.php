@extends('layouts.admin.app')

@section('title', 'My Exams')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-file-signature text-indigo-600 dark:text-indigo-400"></i><span>My Exams</span></h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose a subject and class to see its exam papers and enter marks.</p>
        </div>
        <a href="{{ route('teacher.exams.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700"><i class="fa-solid fa-plus mr-2"></i>Add an Exam</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="flex items-center gap-3 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-semibold text-emerald-800 dark:text-emerald-200"><i class="fa-solid fa-circle-check text-lg"></i><span>{{ session('success') }}</span></div>@endif
    @if(session('error'))<div class="flex items-center gap-3 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-sm font-semibold text-rose-800 dark:text-rose-200"><i class="fa-solid fa-circle-exclamation text-lg"></i><span>{{ session('error') }}</span></div>@endif

    <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 px-5 py-4 shadow-xs">
        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400"><i class="fa-solid fa-calendar-days"></i></span><div><p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">School Term</p><p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $selectedTerm->term_name ?? 'All terms' }} @if($selectedTerm) · {{ $selectedTerm->academic_year ?? '' }} @endif</p></div></div>
        <form method="GET" action="{{ route('teacher.exams.index') }}" class="flex items-center gap-2">
            <label for="term_id" class="sr-only">Filter by term</label>
            <select id="term_id" name="term_id" onchange="this.form.submit()" class="min-w-48 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($terms as $term)<option value="{{ $term->id }}" @selected(($selectedTerm->id ?? null) == $term->id)>{{ $term->term_name }} {{ $term->academic_year ? '· '.$term->academic_year : '' }}</option>@endforeach
            </select>
            @if(request()->filled('term_id'))<a href="{{ route('teacher.exams.index') }}" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Reset</a>@endif
        </form>
    </section>

    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400"><span class="rounded-full bg-indigo-50 dark:bg-indigo-950/50 px-3 py-1.5 text-indigo-700 dark:text-indigo-300">{{ count($subjectGroups) }} assigned {{ \Illuminate\Support\Str::plural('subject', count($subjectGroups)) }}</span><span class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5">{{ $exams->count() }} papers in this term</span></div>

    @forelse($subjectGroups as $subjectGroup)
        <details class="group overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-slate-50/70 dark:bg-slate-800/90 px-5 py-4 hover:bg-slate-100 dark:hover:bg-slate-700/40">
                <span class="flex min-w-0 items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300"><i class="fa-solid fa-book-open"></i></span><span class="min-w-0"><span class="block font-bold text-slate-800 dark:text-slate-100">{{ $subjectGroup['subject']->subject_name ?? 'Assigned subject' }}</span><span class="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">{{ $subjectGroup['subject']->subject_code ?? 'Subject allocation' }}</span></span></span>
                <span class="flex shrink-0 items-center gap-3"><span class="rounded-full bg-indigo-100 dark:bg-indigo-950/70 px-2.5 py-1 text-[10px] font-bold text-indigo-700 dark:text-indigo-300">{{ count($subjectGroup['classes']) }} {{ \Illuminate\Support\Str::plural('class', count($subjectGroup['classes'])) }}</span><i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform group-open:rotate-180"></i></span>
            </summary>

            <div class="space-y-4 border-t border-slate-200/80 dark:border-slate-700/60 p-4 sm:p-5">
                @if($subjectGroup['subject'])
                    <div class="flex justify-end">
                        <a href="{{ route('teacher.subjects.marks', $subjectGroup['subject']->id) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700"><i class="fa-solid fa-chart-column mr-2"></i>View All Marks for {{ $subjectGroup['subject']->subject_name }}</a>
                    </div>
                @endif
                @foreach($subjectGroup['classes'] as $classGroup)
                    <details open class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white dark:bg-slate-800 px-4 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <span class="flex min-w-0 items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300"><i class="fa-solid fa-school"></i></span><span class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $classGroup['label'] }}</span></span>
                            <span class="shrink-0 rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-[10px] font-bold text-slate-600 dark:text-slate-300">{{ $classGroup['papers']->count() }} {{ \Illuminate\Support\Str::plural('paper', $classGroup['papers']->count()) }}</span>
                        </summary>
                        <div class="border-t border-slate-200 dark:border-slate-700">
                            @forelse($classGroup['papers'] as $exam)
                                <div class="flex flex-col gap-3 border-b border-slate-100 dark:border-slate-700/60 px-4 py-4 last:border-b-0 lg:flex-row lg:items-center lg:justify-between hover:bg-slate-50/70 dark:hover:bg-slate-700/20">
                                    <div class="flex min-w-0 items-start gap-3"><span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300"><i class="fa-solid fa-file-lines"></i></span><div class="min-w-0"><h3 class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $exam->exam_name }}</h3><p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ $exam->exam_date->format('d M Y') }} <span class="mx-1">·</span> Out of {{ $exam->max_marks ?? 100 }} marks <span class="mx-1">·</span> Marks entered for {{ $exam->marks_count }} students</p></div></div>
                                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                        @if($exam->marks_count > 0)<span class="rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 dark:text-emerald-300">Marks entered</span>@else<span class="rounded-full bg-amber-50 dark:bg-amber-950/50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700 dark:text-amber-300">No marks yet</span>@endif
                                        <a href="{{ route('teacher.marks.manage', $exam->id) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-pen-to-square mr-1.5"></i>{{ $exam->marks_count ? 'View or edit marks' : 'Enter marks' }}</a>
                                        <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Delete this paper and all recorded marks?');">@csrf @method('DELETE')<button type="submit" aria-label="Delete {{ $exam->exam_name }}" class="rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/30"><i class="fa-solid fa-trash-can"></i></button></form>
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-7 text-center"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No exams for this class and term.</p><a href="{{ route('teacher.exams.create') }}" class="mt-3 inline-flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline"><i class="fa-solid fa-plus mr-1.5"></i>Add an Exam</a></div>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </div>
        </details>
    @empty
        <section class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/70 dark:bg-slate-800/50 px-6 py-14 text-center"><span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400"><i class="fa-solid fa-book-open"></i></span><h2 class="font-bold text-slate-800 dark:text-slate-100">No subjects assigned yet</h2><p class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">Ask the school administrator to assign your subjects and classes.</p></section>
    @endforelse
</div>
@endsection
