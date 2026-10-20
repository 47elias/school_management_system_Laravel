@extends('layouts.student')

@section('content')
<div class="space-y-4">
    <!-- PAGE TITLE -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-300 dark:border-slate-800">
        <div>
            <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">Exam Results</h1>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Subject marks, paper breakdowns, and remarks</p>
        </div>

        @if($activeTerm)
        <form method="GET" action="{{ url()->current() }}">
            <div class="relative">
                <i class="fa-solid fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-900 text-xs pointer-events-none"></i>
                <select name="term_id" 
                        onchange="this.form.submit()"
                        class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg pl-8 pr-8 py-1.5 text-xs font-black text-slate-900 dark:text-slate-100 focus:outline-none">
                    @foreach($allTerms as $t)
                        <option value="{{ $t->id }}" {{ ($activeTerm->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year ?? $t->academicYear->year_name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
        @endif
    </div>

    @if(!$activeTerm)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm font-semibold text-amber-950 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
            No term in your enrolment period is currently available. Exam results will appear here when they are recorded for an eligible term.
        </div>
    @else

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- TERM AVERAGE STAT WIDGET -->
        <div class="bg-slate-900 text-white rounded-lg p-4 shadow-xs flex flex-col justify-between border border-slate-900">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-300 block">Term Average</span>
                <span class="text-3xl font-black mt-1 block">{{ round($average, 1) }}%</span>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-700 text-xs space-y-0.5">
                <p class="font-black truncate text-white">{{ $student->surname }}, {{ $student->name }}</p>
                <p class="text-[11px] font-bold text-slate-300">Grade: {{ $student->grade }} | ID: {{ $student->student_number ?? $student->student_id }}</p>
            </div>
        </div>

        <!-- RESULTS TABLE -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 bg-slate-100 dark:bg-slate-950">
                <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                    <i class="fa-solid fa-graduation-cap text-slate-900"></i>
                    <span>Academic Performance: {{ $activeTerm->term_name }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-900 dark:text-slate-200">
                    <thead class="bg-slate-100 dark:bg-slate-950 text-[10px] font-black uppercase text-slate-900 dark:text-slate-300 border-b border-slate-300 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-4">Subject</th>
                            <th class="py-2.5 px-4">Papers</th>
                            <th class="py-2.5 px-4">Teacher Comments</th>
                            <th class="py-2.5 px-4 text-center">Score</th>
                            <th class="py-2.5 px-4 text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($current_results as $result)
                            <tr class="hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="py-2.5 px-4 font-black text-slate-900 dark:text-slate-100">
                                    {{ $result->subject_name }}
                                    @if($result->papers_taken > 1)
                                        <span class="block text-[10px] font-bold text-slate-700 dark:text-slate-400 mt-0.5">{{ $result->papers_taken }} Papers</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 space-y-1">
                                    @foreach($result->individual as $mark)
                                        <div class="flex items-center justify-between px-2 py-1 rounded bg-slate-200 dark:bg-slate-800 text-[10px] border border-slate-300 dark:border-slate-700">
                                            <span class="font-bold text-slate-900 dark:text-slate-300 truncate max-w-[100px]">{{ $mark->exam->exam_name }}</span>
                                            @php $paperMax = (float) ($mark->max_score ?: $mark->exam->max_marks ?: 0); @endphp
                                            <span class="font-mono font-black text-slate-900 dark:text-slate-100">{{ $mark->score }} / {{ $paperMax ?: '—' }}{{ $paperMax > 0 ? ' (' . number_format(($mark->score / $paperMax) * 100, 1) . '%)' : '' }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="py-2.5 px-4">
                                    @php $hasComments = false; @endphp
                                    @foreach($result->individual as $mark)
                                        @if(!empty($mark->teacher_comment))
                                            @php $hasComments = true; @endphp
                                            <div class="p-2 rounded bg-amber-100 dark:bg-amber-950/40 border-l-4 border-amber-600 text-xs font-bold text-slate-900 dark:text-amber-200 mb-1">
                                                "{{ $mark->teacher_comment }}"
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$hasComments)
                                        <span class="text-slate-700 dark:text-slate-400 font-bold italic text-[11px]">No remarks</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono font-black text-xs text-slate-900 dark:text-slate-100">
                                    {{ $result->average_score }}%
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @php
                                        $badgeStyle = 'bg-rose-200 text-rose-900 border-rose-400';
                                        if($result->grade == 'A') $badgeStyle = 'bg-emerald-200 text-emerald-950 border-emerald-400';
                                        elseif($result->grade == 'B') $badgeStyle = 'bg-sky-200 text-sky-950 border-sky-400';
                                        elseif($result->grade == 'C') $badgeStyle = 'bg-amber-200 text-amber-950 border-amber-400';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded text-xs font-black border {{ $badgeStyle }}">
                                        {{ $result->grade }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-900 dark:text-slate-400 font-bold">
                                    No results published for this term.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
