{{-- resources/views/exams/subject_report.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Subject Analytics Report | ' . $subject->subject_name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-book-open text-indigo-600 dark:text-indigo-400"></i>
                <span>{{ $subject->subject_name }} Performance Analytics</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Subject Code: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $subject->subject_code }}</strong> &bull; 
                Session: <strong class="text-slate-700 dark:text-slate-200">{{ $selectedTerm->term_name ?? 'Current Term' }} ({{ $selectedTerm->academic_year ?? date('Y') }})</strong>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            {{-- Academic Session Selector --}}
            <form method="GET" action="{{ route('exams.subject_report', $subject->id) }}" class="flex items-center space-x-2">
                <select name="term_id" onchange="this.form.submit()" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }})
                        </option>
                    @endforeach
                </select>
            </form>

            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center">
                <i class="fa-solid fa-print mr-2"></i> Print Report
            </button>

            <a href="{{ route('exams.index') }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Overall Performance KPI Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Overall Subject Mean --}}
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white p-5 rounded-3xl shadow-sm border border-indigo-500/30">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-200">Subject Mean Score</span>
                    <i class="fa-solid fa-calculator text-indigo-300 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black font-mono mt-2">{{ $overallAverage }}%</h3>
                <p class="text-[11px] text-indigo-200 mt-1">School-wide average score</p>
            </div>

            {{-- Pass Rate --}}
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-5 rounded-3xl shadow-sm border border-emerald-500/30">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-200">Overall Pass Rate</span>
                    <i class="fa-solid fa-circle-check text-emerald-300 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black font-mono mt-2">{{ $overallPassRate }}%</h3>
                <p class="text-[11px] text-emerald-200 mt-1">Passing standard achieved</p>
            </div>

            {{-- Candidates Sat --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider">Total Candidates Sat</span>
                    <i class="fa-solid fa-user-graduate text-indigo-500 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black font-mono text-slate-800 dark:text-slate-100 mt-2">{{ $totalCandidates }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Evaluated across all streams</p>
            </div>

            {{-- Papers Scheduled --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider">Exam Papers Written</span>
                    <i class="fa-solid fa-file-signature text-amber-500 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black font-mono text-slate-800 dark:text-slate-100 mt-2">{{ $paperBreakdown->count() }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Papers recorded for this subject</p>
            </div>
        </div>

        {{-- SECTION 1: OVERVIEW OF EXAM PAPERS WRITTEN FOR THIS SUBJECT --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-file-signature text-amber-400 mr-2.5"></i>
                    <span>Scheduled Exam Papers & Paper Performance Breakdown</span>
                </h3>
                <span class="text-[10px] font-mono bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-0.5 rounded-full font-bold">
                    {{ $paperBreakdown->count() }} {{ Str::plural('Paper', $paperBreakdown->count()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paper / Exam Title</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target Stream / Scope</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidates Sat</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paper Average (%)</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pass Rate (%)</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peak Score</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($paperBreakdown as $paper)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $paper->exam_name }}</strong>
                                    <span class="text-[10px] font-mono text-slate-400">ID: #{{ $paper->exam_id }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-chalkboard text-slate-400 mr-1.5"></i> {{ $paper->class_name }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-600 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($paper->exam_date)->format('d M, Y') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-700 dark:text-slate-200">
                                    {{ $paper->sat_count }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-sm text-indigo-600 dark:text-indigo-400">
                                    {{ $paper->avg_score }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $paper->pass_rate }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-800 dark:text-slate-100">
                                    {{ $paper->highest }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($paper->status === 'published')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                            Pending Marks
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic">
                                    No exam papers written or scheduled for {{ $subject->subject_name }} in this academic term.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION 2: PER CLASS STREAM PERFORMANCE OVERVIEW --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-layer-group text-indigo-400 mr-2.5"></i>
                    <span>Stream Comparison Overview for {{ $subject->subject_name }}</span>
                </h3>
                <span class="text-[10px] font-mono bg-indigo-950 text-indigo-300 border border-indigo-800/60 px-2.5 py-0.5 rounded-full font-bold">
                    {{ $streamBreakdown->count() }} {{ Str::plural('Stream', $streamBreakdown->count()) }} Evaluated
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Class Stream</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidates Evaluated</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stream Mean Score (%)</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pass Rate (%)</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Highest Score</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Top Stream Performer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($streamBreakdown as $stream)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                        <i class="fa-solid fa-chalkboard text-indigo-500 mr-2"></i> {{ $stream->class_name }}
                                    </strong>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-700 dark:text-slate-200">
                                    {{ $stream->students_count }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-sm text-indigo-600 dark:text-indigo-400">
                                    {{ $stream->avg_score }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $stream->pass_rate }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-800 dark:text-slate-100">
                                    {{ $stream->highest_score }}%
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-trophy text-amber-500 mr-1.5"></i> {{ $stream->top_student }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    No stream performance records found for {{ $subject->subject_name }} in this term.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION 3: OVERALL GRADE DISTRIBUTION MATRIX --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-chart-pie text-emerald-500 mr-2"></i> Overall Grade Distribution
                </h3>
                <span class="text-[10px] font-mono text-slate-400">Subject Grade Spread</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                @foreach(['A' => 'emerald', 'B' => 'teal', 'C' => 'sky', 'D' => 'indigo', 'E' => 'amber', 'F' => 'rose'] as $gradeKey => $color)
                    <div class="p-4 rounded-2xl bg-{{ $color }}-50 dark:bg-{{ $color }}-950/40 border border-{{ $color }}-200/80 dark:border-{{ $color }}-800/50">
                        <span class="text-xs font-extrabold uppercase text-{{ $color }}-700 dark:text-{{ $color }}-400 block">Grade {{ $gradeKey }}</span>
                        <h4 class="text-2xl font-black font-mono text-{{ $color }}-900 dark:text-{{ $color }}-200 mt-1">{{ $gradeCounts[$gradeKey] ?? 0 }}</h4>
                        <span class="text-[9px] font-bold text-{{ $color }}-600/80 dark:text-{{ $color }}-400 block mt-0.5">
                            {{ $totalCandidates > 0 ? round((($gradeCounts[$gradeKey] ?? 0) / $totalCandidates) * 100, 1) : 0 }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection