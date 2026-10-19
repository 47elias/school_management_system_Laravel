{{-- resources/views/exams/report.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Exam Report | ' . ($exam->subject?->subject_name ?? 'Subject'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Class Performance Report</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Official Subject Analytics & Rankings</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-2.5">
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">
                <i class="fa-solid fa-print mr-2"></i> Print Official Report
            </button>
            <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Schedule
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 sm:p-8 transition-colors">
            
            {{-- Official Header --}}
            <div class="text-center border-b border-slate-200 dark:border-slate-700 pb-6 mb-6">
                <h1 class="text-2xl font-black uppercase text-slate-900 dark:text-white tracking-wide">{{ env('SCHOOL_NAME', 'ACADEMIC INSTITUTION') }}</h1>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-1">OFFICIAL SUBJECT PERFORMANCE REPORT</p>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 font-medium">
                    <strong>Term:</strong> {{ $exam->term?->term_name ?? 'N/A' }} |
                    <strong>Subject:</strong> {{ $exam->subject?->subject_name }} |
                    <strong>Paper:</strong> {{ $exam->exam_name }} |
                    <strong>Scope:</strong> {{ $targetLabel }} |
                    <strong>Generated:</strong> {{ date('d M, Y') }}
                </p>
            </div>

            {{-- Statistics Summary --}}
            @php
                $calcService = new \App\Services\ExamCalculationService();
                $allScores = $students->map(fn($s) => $s->marks->firstWhere('exam_id', $exam->id)->score ?? 0);
                $avg = $allScores->count() > 0 ? $allScores->avg() : 0;
                $max = $allScores->count() > 0 ? $allScores->max() : 0;
                $min = $allScores->count() > 0 ? $allScores->min() : 0;
            @endphp
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Class Average</span>
                    <span class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 block">{{ number_format($avg, 1) }}%</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Highest Score</span>
                    <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $max }}%</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lowest Score</span>
                    <span class="text-xl font-extrabold text-rose-600 dark:text-rose-400 mt-1 block">{{ $min }}%</span>
                </div>
            </div>

            {{-- Ranking Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">Rank</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Name</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Score (%)</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Grade</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teacher Comment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @php
                            $sortedStudents = $students->sortByDesc(function($s) use ($exam) {
                                return $s->marks->firstWhere('exam_id', $exam->id)->score ?? -1;
                            });
                            $currentRank = 0;
                            $lastScore = null;
                            $displayRank = 0;
                        @endphp

                        @foreach($sortedStudents as $student)
                            @php
                                $mark = $student->marks->firstWhere('exam_id', $exam->id);
                                $score = $mark ? $mark->score : 0;

                                $currentRank++;
                                if ($score !== $lastScore) {
                                    $displayRank = $currentRank;
                                }
                                $lastScore = $score;

                                $gradeInfo = $calcService->calculateGrade($score);
                                $gradeLetter = $gradeInfo['grade'];
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">#{{ $displayRank }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                    {{ strtoupper($student->surname) }}, {{ $student->name }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $mark ? number_format($mark->score, 0) : '0' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(in_array($gradeLetter, ['A', 'B']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">{{ $gradeLetter }}</span>
                                    @elseif($gradeLetter == 'C')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50">{{ $gradeLetter }}</span>
                                    @elseif(in_array($gradeLetter, ['D', 'E']))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">{{ $gradeLetter }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">{{ $gradeLetter }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $mark->teacher_comment ?? '---' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Signature Section --}}
            <div class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-700">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center text-xs font-bold text-slate-700 dark:text-slate-300">
                    <div>
                        <div class="border-t border-slate-400 dark:border-slate-600 pt-2 w-48 mx-auto">Subject Educator</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Signature & Date</span>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 dark:border-slate-600 pt-2 w-48 mx-auto">Head of Department</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Signature & Date</span>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 dark:border-slate-600 pt-2 w-48 mx-auto">School Stamp</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Official Seal</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection