{{-- resources/views/exams/enter_marks.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Record Exam Marks | ' . $exam->exam_name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Record Marks: {{ $exam->exam_name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Subject: <strong class="text-slate-700 dark:text-slate-200">{{ $exam->subject->subject_name }}</strong> | Target Grade: <strong class="text-slate-700 dark:text-slate-200">{{ $grade }}</strong>
            </p>
        </div>

        <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
           class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Exams
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <form action="{{ Route::has('exams.bulkStore') ? route('exams.bulkStore') : url('/exams/bulk-store') }}" method="POST">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-pen-to-square text-indigo-500 mr-2.5"></i> Student Mark Ledger
                    </h3>
                    <span class="text-xs font-bold text-slate-400 uppercase">Max Score Default: 100</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Reg No</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Name</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Score Obtained</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Max Score</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teacher Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($students as $st)
                            @php $existing = $marks->get($st->id); @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-3.5 whitespace-nowrap font-mono font-bold text-slate-500 dark:text-slate-400">
                                    {{ $st->student_number }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $st->surname }}, {{ $st->name }}</strong>
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           name="marks[{{ $st->id }}][score]" 
                                           value="{{ $existing ? $existing->score : '' }}" 
                                           placeholder="0.00"
                                           class="w-28 px-3 py-1.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <input type="number" 
                                           name="marks[{{ $st->id }}][max_score]" 
                                           value="{{ $existing ? $existing->max_score : 100 }}" 
                                           class="w-20 px-3 py-1.5 text-xs font-mono rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <input type="text" 
                                           name="marks[{{ $st->id }}][comment]" 
                                           value="{{ $existing ? $existing->teacher_comment : '' }}" 
                                           placeholder="Optional comment..."
                                           class="w-full max-w-xs px-3 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    No students found for grade: {{ $grade }}.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase shadow-sm transition-all">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Marks
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection