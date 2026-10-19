{{-- resources/views/exams/enter_marks.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Record Exam Marks | ' . $exam->exam_name)

@section('header')
    <div x-data="{ showImportModal: false }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Record Marks: {{ $exam->exam_name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Subject: <strong class="text-slate-700 dark:text-slate-200">{{ $exam->subject->subject_name }}</strong> | Target Scope: <strong class="text-indigo-600 dark:text-indigo-400">{{ $targetLabel }}</strong>
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            {{-- Download Excel Template (.xlsx) --}}
            <a href="{{ route('exams.download_template', ['exam_id' => $exam->id, 'target' => $target, 'format' => 'xlsx']) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-file-excel mr-2"></i> Download Excel (.xlsx)
            </a>

            {{-- Download CSV Template --}}
            <a href="{{ route('exams.download_template', ['exam_id' => $exam->id, 'target' => $target, 'format' => 'csv']) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-file-csv mr-2"></i> Download CSV
            </a>

            {{-- Import Marks Button --}}
            <button @click="$dispatch('open-import-modal')" type="button" 
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-file-import mr-2"></i> Import Excel / CSV
            </button>

            <a href="{{ Route::has('exams.report') ? route('exams.report', ['exam_id' => $exam->id, 'grade' => $target]) : url('/exams/' . $exam->id . '/report/' . $target) }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-chart-column mr-2"></i> Report
            </a>

            <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            showImportModal: false,
            marksCount: 0,
            updateStats() {
                let inputs = document.querySelectorAll('.mark-input');
                let count = 0;
                inputs.forEach(i => { if (i.value !== '') count++; });
                this.marksCount = count;
            }
         }" 
         @open-import-modal.window="showImportModal = true"
         x-init="updateStats()" 
         class="space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-bold">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-bold">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        {{-- MANUAL ENTRY FORM --}}
        <form action="{{ Route::has('exams.bulkStore') ? route('exams.bulkStore') : url('/exams/bulk-store') }}" method="POST">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                
                {{-- Header Status Bar --}}
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-pen-to-square text-amber-400 text-base"></i>
                        <h3 class="text-sm font-bold text-white">Student Mark Ledger Entry</h3>
                    </div>
                    <div class="flex items-center space-x-3 text-xs font-mono">
                        <span class="bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">
                            Total Students: <strong>{{ $students->count() }}</strong>
                        </span>
                        <span class="bg-indigo-950 text-indigo-300 px-3 py-1 rounded-full border border-indigo-800">
                            Entered: <strong x-text="marksCount"></strong>/{{ $students->count() }}
                        </span>
                    </div>
                </div>

                {{-- Marks Ledger Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reg Number</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Name</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Class Stream</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Score Obtained</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Max Score</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Educator Remark</th>
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
                                <td class="px-6 py-3.5 whitespace-nowrap font-bold text-slate-600 dark:text-slate-300">
                                    {{ $st->schoolClass->class_name ?? $st->grade }}
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           name="marks[{{ $st->id }}][score]" 
                                           value="{{ $existing ? $existing->score : '' }}" 
                                           placeholder="0.00"
                                           @input="updateStats()"
                                           class="mark-input w-28 px-3 py-1.5 text-xs font-mono font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                                           class="w-full max-w-xs px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    No students enrolled in {{ $targetLabel }}.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase shadow-sm transition-all flex items-center">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Marks Ledger
                    </button>
                </div>
            </div>
        </form>

        {{-- IMPORT MARKS MODAL --}}
        <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showImportModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700 space-y-4">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-file-import text-amber-500 mr-2"></i> Import Marks (.xlsx, .xls, .csv)
                        </h3>
                        <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <form action="{{ route('exams.import_marks') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                        <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/50 text-xs space-y-2">
                            <p class="font-bold text-indigo-900 dark:text-indigo-200 flex items-center">
                                <i class="fa-solid fa-circle-info mr-1.5 text-indigo-500"></i> Import Instructions:
                            </p>
                            <ol class="list-decimal list-inside space-y-1 text-slate-600 dark:text-slate-300">
                                <li>Click <strong>Download Excel (.xlsx)</strong> to get the pre-formatted student list.</li>
                                <li>Enter numerical marks into the <code>score</code> column in Microsoft Excel.</li>
                                <li>Select and upload the saved <strong>.xlsx</strong>, <strong>.xls</strong>, or <strong>.csv</strong> file below.</li>
                            </ol>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Select Excel / CSV Spreadsheet <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" name="marks_file" required accept=".xlsx,.xls,.csv,.txt"
                                   class="w-full px-3 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="showImportModal = false" 
                                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all flex items-center">
                                <i class="fa-solid fa-upload mr-2"></i> Upload & Import Spreadsheet
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
@endsection