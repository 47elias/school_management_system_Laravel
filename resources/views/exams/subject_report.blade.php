{{-- resources/views/exams/subject_report.blade.php --}}
@extends('layouts.admin.app')

@section('title', ($teacherView ? 'Subject Marks' : 'Subject Report') . ' | ' . $subject->subject_name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-book-open text-indigo-600 dark:text-indigo-400"></i>
                <span>{{ $subject->subject_name }} {{ $teacherView ? 'Marks' : 'Performance Report' }}</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Subject Code: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $subject->subject_code }}</strong> &bull; 
                School Term: <strong class="text-slate-700 dark:text-slate-200">{{ $selectedTerm->term_name ?? 'Current Term' }} ({{ $selectedTerm->academic_year ?? date('Y') }})</strong>
                @if($selectedGrade) &bull; Grade: <strong class="text-slate-700 dark:text-slate-200">{{ $selectedGrade }}</strong> @endif
            </p>
        </div>

        <div class="flex items-center space-x-3">
            {{-- Academic Session Selector --}}
            <form method="GET" action="{{ $teacherView ? route('teacher.subjects.marks', $subject->id) : route('exams.subject_report', $subject->id) }}" class="flex items-center space-x-2">
                @if($selectedGrade)<input type="hidden" name="grade" value="{{ $selectedGrade }}">@endif
                @if($selectedClassId)<input type="hidden" name="class_id" value="{{ $selectedClassId }}">@endif
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

            <a href="{{ $teacherView ? route('teacher.exams.index') : route('exams.index') }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        @if(!$teacherView)
        {{-- Overall Performance KPI Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Overall Subject Mean --}}
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white p-5 rounded-3xl shadow-sm border border-indigo-500/30">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-200">Subject Mean Score</span>
                    <i class="fa-solid fa-calculator text-indigo-300 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black font-mono mt-2">{{ $overallAverage }}%</h3>
                <p class="text-[11px] text-indigo-200 mt-1">Average for selected grade and class</p>
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
        @endif

        {{-- All student marks across the subject's papers --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">All Marks for {{ $subject->subject_name }}</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $marksForTable->count() }} marks from {{ $exams->count() }} papers in this term</p>
                </div>
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-indigo-700 no-print"><i class="fa-solid fa-print mr-2"></i>Print Marks</button>
            </div>

            <form method="GET" action="{{ $teacherView ? route('teacher.subjects.marks', $subject->id) : route('exams.subject_report', $subject->id) }}" class="grid grid-cols-1 gap-3 border-b border-slate-200/80 dark:border-slate-700/60 p-4 sm:grid-cols-2 xl:grid-cols-7 no-print">
                @if($selectedTerm)<input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">@endif
                <label class="xl:col-span-2"><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Find student</span><input type="search" name="search" value="{{ $markSearch }}" placeholder="Name or student number" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100"></label>
                <label><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Paper</span><select name="exam_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100"><option value="">All papers</option>@foreach($exams as $exam)<option value="{{ $exam->id }}" @selected((string) $selectedExamId === (string) $exam->id)>{{ $exam->exam_name }}</option>@endforeach</select></label>
                <label><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Grade / Form</span><select name="grade" onchange="this.form.class_id.value = ''; this.form.exam_id.value = ''; this.form.submit()" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100">@foreach($reportGrades as $grade)<option value="{{ $grade }}" @selected($selectedGrade === $grade)>{{ $grade }}</option>@endforeach</select></label>
                <label><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Stream / Class</span><select name="class_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100"><option value="">All streams in {{ $selectedGrade ?? 'this grade' }}</option>@foreach($reportClasses as $class)<option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>{{ $class->class_name }}</option>@endforeach</select></label>
                <label><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Sort by</span><select name="sort" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100"><option value="student" @selected($sortBy === 'student')>Student name</option><option value="class" @selected($sortBy === 'class')>Class</option><option value="paper" @selected($sortBy === 'paper')>Paper</option><option value="mark" @selected($sortBy === 'mark')>Mark</option><option value="date" @selected($sortBy === 'date')>Exam date</option></select></label>
                <div class="flex items-end gap-2"><label class="min-w-0 flex-1"><span class="mb-1 block text-[11px] font-bold text-slate-600 dark:text-slate-300">Order</span><select name="direction" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100"><option value="asc" @selected($sortDirection === 'asc')>A to Z / Low to high</option><option value="desc" @selected($sortDirection === 'desc')>Z to A / High to low</option></select></label><button type="submit" class="rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-700">Apply</button></div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50/80 dark:bg-slate-900/50 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Student No.</th><th class="px-4 py-3">Class</th><th class="px-4 py-3">Paper</th><th class="px-4 py-3">Exam Date</th><th class="px-4 py-3 text-center">Mark</th><th class="px-4 py-3 text-center">Grade</th><th class="px-4 py-3">Comment</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($marksForTable as $mark)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-800 dark:text-slate-100">{{ $mark->student->surname ?? '' }}, {{ $mark->student->name ?? '' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-500 dark:text-slate-400">{{ $mark->student->student_number ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-300">{{ $mark->student->schoolClass->class_name ?? 'Unassigned' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700 dark:text-slate-200">{{ $mark->exam->exam_name ?? 'Exam paper' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-300">{{ $mark->exam?->exam_date?->format('d M Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-center font-black text-slate-800 dark:text-slate-100">{{ $mark->score }} / {{ $mark->max_score ?? $mark->exam->max_marks ?? 100 }}</td>
                                <td class="px-4 py-3 text-center"><span class="rounded-lg bg-indigo-50 px-2 py-1 font-bold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">{{ $mark->grade_letter }}</span></td>
                                <td class="min-w-40 px-4 py-3 text-slate-500 dark:text-slate-400">{{ $mark->teacher_comment ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No marks match these filters for this term.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
@endsection
