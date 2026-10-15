{{-- resources/views/marks/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Enter Marks | ' . ($exam->subject?->subject_name ?? 'Subject'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                Marks Entry: <span class="text-indigo-600 dark:text-indigo-400">{{ $exam->subject?->subject_name }}</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ $exam->exam_name }} — Grade: <strong class="text-slate-800 dark:text-slate-200">{{ $grade }}</strong>
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Schedule
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
        total: {{ count($students) }},
        scores: {},
        get filledCount() {
            return Object.values(this.scores).filter(v => v !== '' && v !== null && v !== undefined).length;
        }
    }" class="space-y-6">

        {{-- Alerts --}}
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Main Container Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            {{-- Header Bar --}}
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-file-pen text-indigo-500 mr-2.5"></i> Score Sheet — {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                </h3>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                        Term: {{ $exam->term?->term_name }}
                    </span>
                    <span :class="filledCount === total && total > 0 ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600'" 
                          class="px-3 py-1 rounded-full text-xs font-bold border">
                        <span x-text="filledCount"></span> / <span x-text="total"></span> Entered
                    </span>
                </div>
            </div>

            <form action="{{ Route::has('marks.bulk_store') ? route('marks.bulk_store') : url('/marks/bulk-store') }}" method="POST" id="marksForm">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                <div class="overflow-x-auto max-h-[65vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/90 sticky top-0 z-10 backdrop-blur-sm">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">#</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Details</th>
                                <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-48">Score (Max {{ $exam->max_marks ?? 100 }})</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teacher's Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($students as $index => $student)
                                @php
                                    $existingMark = $student->marks->firstWhere('exam_id', $exam->id);
                                    $score = $existingMark ? $existingMark->score : '';
                                    $comment = $existingMark ? $existingMark->teacher_comment : '';
                                    $avatarBg = ($student->gender == 'Female') ? 'ec4899' : '3b82f6';
                                    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($student->name . ' ' . $student->surname) . "&background={$avatarBg}&color=fff&size=80";
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-init="scores[{{ $student->id }}] = '{{ $score }}'">
                                    <td class="px-6 py-4 text-center font-mono font-bold text-slate-400">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ $student->photo_path ? asset('storage/' . $student->photo_path) : $avatarUrl }}" 
                                                 alt="{{ $student->name }}" 
                                                 class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                                            <div>
                                                <strong class="text-xs font-bold text-slate-900 dark:text-white block uppercase tracking-wide">
                                                    {{ $student->surname }}, {{ $student->name }}
                                                </strong>
                                                <span class="text-[11px] font-mono text-slate-400 block mt-0.5">
                                                    {{ $student->student_number ?? $student->student_id }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative rounded-xl shadow-sm max-w-[140px] mx-auto">
                                            <input type="number" 
                                                   name="marks[{{ $student->id }}][score]"
                                                   x-model="scores[{{ $student->id }}]"
                                                   :class="scores[{{ $student->id }}] !== '' && scores[{{ $student->id }}] !== null ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-400 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100'"
                                                   class="w-full pl-3 pr-8 py-2 text-xs text-center font-bold rounded-xl border focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                                   min="0" max="{{ $exam->max_marks ?? 100 }}" step="0.1"
                                                   placeholder="0-{{ $exam->max_marks ?? 100 }}">
                                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400 text-[10px] font-bold">
                                                %
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" 
                                               name="marks[{{ $student->id }}][comment]"
                                               value="{{ $comment }}"
                                               placeholder="e.g. Excellent progress, keep it up"
                                               class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                                            <i class="fa-solid fa-user-slash text-xl"></i>
                                        </div>
                                        <h4 class="font-bold text-slate-700 dark:text-slate-300">No Students Found</h4>
                                        <p class="text-xs text-slate-400 mt-1">No students are assigned to Grade {{ $grade }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Sticky Footer --}}
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                    <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save All Marks
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection