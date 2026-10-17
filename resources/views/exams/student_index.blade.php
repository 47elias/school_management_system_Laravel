{{-- resources/views/exams/student_index.blade.php --}}
@extends('layouts.student.app')

@section('title', 'My Academic Results | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Academic Performance</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Student: <strong class="text-slate-700 dark:text-slate-200">{{ $student->name }} {{ $student->surname }}</strong> ({{ $student->student_number }})
            </p>
        </div>

        {{-- Term Selector --}}
        <form method="GET" action="{{ Route::has('exams.studentResults') ? route('exams.studentResults') : url('/student/results') }}">
            <select name="term_id" onchange="this.form.submit()" 
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                @foreach($allTerms as $t)
                    <option value="{{ $t->id }}" {{ $activeTerm && $activeTerm->id == $t->id ? 'selected' : '' }}>
                        {{ $t->term_name }} ({{ $t->academic_year }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Overall Summary KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-5 text-white shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Overall Term Average</span>
                <h3 class="text-3xl font-black mt-2 font-mono">{{ $average }}%</h3>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Overall Grade & Remark</span>
                <div class="flex items-baseline space-x-2 mt-2">
                    <h3 class="text-3xl font-black font-mono">{{ $overallGrade['grade'] ?? 'N/A' }}</h3>
                    <span class="text-xs font-bold opacity-90">({{ $overallGrade['remark'] ?? '' }})</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Evaluated Subjects</span>
                <h3 class="text-3xl font-black mt-2 font-mono">{{ $current_results->count() }}</h3>
            </div>
        </div>

        {{-- Active Term Breakdown Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-graduation-cap text-indigo-500 mr-2.5"></i> {{ $activeTerm->term_name ?? 'Current Term' }} Results
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-900 text-white dark:bg-slate-950">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Paper Breakdown</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold uppercase tracking-wider">Papers Count</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold uppercase tracking-wider">Final Average</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold uppercase tracking-wider">Grade</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($current_results as $res)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $res->subject_name }}</strong>
                                <span class="text-[10px] font-mono text-slate-400">{{ $res->subject_code }}</span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($res->papers as $paper)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                            <span class="font-bold mr-1">{{ $paper['exam_name'] }}:</span>
                                            <span class="font-mono">{{ $paper['score'] }}/{{ $paper['max_score'] }} ({{ $paper['percentage'] }}%)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-600 dark:text-slate-300">
                                {{ $res->papers_taken }} {{ Str::plural('paper', $res->papers_taken) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-black text-sm text-indigo-600 dark:text-indigo-400">
                                {{ $res->average_score }}%
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-slate-800 dark:text-slate-100">
                                {{ $res->grade }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($res->is_passed)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Pass
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                        Fail
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                No exam records found for this term.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection