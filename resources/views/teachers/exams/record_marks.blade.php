{{-- resources/views/teachers/exams/record_marks.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Record Marks | ' . $exam->exam_name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-square-poll-vertical text-emerald-600 dark:text-emerald-400"></i>
                <span>Record Marks: {{ $exam->exam_name }}</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Subject: <strong class="text-slate-700 dark:text-slate-200">{{ $exam->subject->subject_name ?? $exam->subject->name ?? 'N/A' }}</strong> &bull; 
                Class: <strong class="text-slate-700 dark:text-slate-200">{{ $exam->schoolClass->class_name ?? $exam->schoolClass->name ?? ($exam->subjectAssignment?->grade ? $exam->subjectAssignment->grade . ' · All Streams' : 'All Streams') }}</strong>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-xs">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Exams
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{
        passMark: {{ $exam->subject->pass_mark ?? 50 }},
        scores: {
            @foreach($students as $st)
                @php $existing = $marks->get($st->id); @endphp
                '{{ $st->id }}': '{{ $existing ? $existing->score : '' }}',
            @endforeach
        },
        getStatus(score) {
            if (score === '' || score === null || isNaN(score)) return 'PENDING';
            return parseFloat(score) >= this.passMark ? 'PASSED' : 'FAILED';
        }
    }" class="space-y-6">

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-bold">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-bold text-rose-800 dark:text-rose-200">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-bold text-rose-800 dark:text-rose-200">{{ $errors->first() }}</div>
        @endif

        <section class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 p-5 shadow-xs lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100"><i class="fa-solid fa-file-excel mr-2 text-emerald-600"></i>Enter Marks with Excel</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Download the class list, enter marks in Excel, then upload the saved file.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('teacher.marks.template', $exam->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700"><i class="fa-solid fa-download mr-2"></i>Download Excel Class List</a>
                <form action="{{ route('teacher.marks.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    @csrf
                    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                    <input type="file" name="marks_file" accept=".xlsx,.xls,.csv,.txt" required class="max-w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-700 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-100">
                    <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-upload mr-2"></i>Upload Marks</button>
                </form>
            </div>
        </section>

        {{-- Main Form Container --}}
        <form action="{{ Route::has('teacher.marks.bulk_store') ? route('teacher.marks.bulk_store') : (Route::has('teachers.exams.bulkStore') ? route('teachers.exams.bulkStore') : '#') }}" method="POST">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-users text-emerald-500 mr-2.5"></i> Class List
                    </h3>

                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Class Marks
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reg No</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Details</th>
                                <th scope="col" class="px-6 py-3.5 text-center font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Score / 100</th>
                                <th scope="col" class="px-6 py-3.5 text-center font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Status</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($students as $st)
                                @php $existing = $marks->get($st->id); @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap font-mono font-bold text-slate-500 dark:text-slate-400">
                                        {{ $st->student_number ?? $st->ec_number ?? 'STU-'.$st->id }}
                                    </td>

                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block uppercase">
                                            {{ $st->surname }}, {{ $st->name }}
                                        </strong>
                                    </td>

                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               max="100" 
                                               name="marks[{{ $st->id }}][score]" 
                                               x-model="scores['{{ $st->id }}']"
                                               placeholder="0.00"
                                               class="w-28 px-3 py-1.5 text-center font-mono font-black text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                                    </td>

                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <template x-if="getStatus(scores['{{ $st->id }}']) === 'PASSED'">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                                Pass
                                            </span>
                                        </template>

                                        <template x-if="getStatus(scores['{{ $st->id }}']) === 'FAILED'">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-black bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                                                Below pass mark
                                            </span>
                                        </template>

                                        <template x-if="getStatus(scores['{{ $st->id }}']) === 'PENDING'">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                                Not entered
                                            </span>
                                        </template>
                                    </td>

                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <input type="text" 
                                               name="marks[{{ $st->id }}][comment]" 
                                               value="{{ $existing ? ($existing->teacher_comment ?? $existing->remarks) : '' }}" 
                                               placeholder="Enter feedback or performance remark..."
                                               class="w-full max-w-sm px-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 italic">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        No students currently enrolled in this class.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/40 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                    <a href="{{ route('teacher.exams.index') }}" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Class Marks</span>
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
