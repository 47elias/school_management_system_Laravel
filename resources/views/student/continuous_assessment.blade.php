@extends('layouts.student')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 border-b border-slate-300 pb-3 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-base font-black text-slate-900 dark:text-slate-100">Class Activities</h1>
            <p class="mt-1 text-xs font-bold text-slate-700 dark:text-slate-400">Your recorded classwork, homework, quizzes and practical work</p>
        </div>
        @if($activeTerm)
        <form method="GET" action="{{ route('student.continuous_assessment') }}">
            <label class="sr-only" for="ca-term">Select term</label>
            <select id="ca-term" name="term_id" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                @foreach($allTerms as $term)
                    <option value="{{ $term->id }}" @selected($activeTerm->id === $term->id)>{{ $term->term_name }} ({{ $term->academic_year }})</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    @if(!$activeTerm)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm font-semibold text-amber-950 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
            No term in your enrolment period is currently available. Your class activity record will appear here when the school opens a term covering your enrolment.
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-300 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase text-slate-600 dark:text-slate-400">Activities recorded</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $activityMarks->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-300 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase text-slate-600 dark:text-slate-400">Subjects with marks</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $subjectSummary->count() }}</p>
            </div>
            <div class="rounded-xl border border-slate-300 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase text-slate-600 dark:text-slate-400">Term</p>
                <p class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $activeTerm->term_name }} <span class="text-sm">({{ $activeTerm->academic_year }})</span></p>
            </div>
        </div>

        @if($subjectSummary->isNotEmpty())
        <section class="rounded-xl border border-slate-300 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-black text-slate-900 dark:text-white">Performance by subject</h2>
            <div class="mt-4 space-y-3">
                @foreach($subjectSummary as $subject)
                    <div>
                        <div class="mb-1 flex justify-between gap-3 text-xs"><span class="font-bold text-slate-800 dark:text-slate-200">{{ $subject->subject }}</span><span class="font-black text-slate-900 dark:text-white">{{ number_format($subject->average, 1) }}%</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"><div class="h-full rounded-full bg-emerald-600" style="width: {{ min(100, max(0, $subject->average)) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-300 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-300 bg-slate-100 px-4 py-3 dark:border-slate-800 dark:bg-slate-950"><h2 class="text-sm font-black text-slate-900 dark:text-white">Activity record</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-600 dark:bg-slate-950 dark:text-slate-400"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Activity</th><th class="px-4 py-3">Type</th><th class="px-4 py-3 text-right">Mark</th><th class="px-4 py-3">Teacher comment</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($activityMarks as $mark)
                            @php $activity = $mark->classActivity; $max = (float) ($activity->max_score ?? 0); @endphp
                            <tr><td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">{{ $activity?->activity_date?->format('d M Y') ?? '—' }}</td><td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $activity?->subjectAssignment?->subject?->subject_name ?? '—' }}</td><td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $activity?->title ?? '—' }}</td><td class="px-4 py-3 capitalize text-slate-700 dark:text-slate-300">{{ $activity?->type ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3 text-right font-black text-slate-900 dark:text-white">{{ $mark->score }} / {{ $max ?: '—' }} @if($max > 0)<span class="text-slate-500">({{ number_format(($mark->score / $max) * 100, 1) }}%)</span>@endif</td><td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $mark->comment ?: '—' }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-slate-600 dark:text-slate-400">No class activity marks have been recorded for this term yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
