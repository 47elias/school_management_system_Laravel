{{-- resources/views/exams/schedule.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Schedule Exam Paper | ' . $targetName)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Schedule Exam: {{ $targetName }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Target Scope: <strong class="text-indigo-600 dark:text-indigo-400">{{ $targetName }}</strong> &bull; 
                Enrolled Students: <strong class="text-slate-700 dark:text-slate-200">{{ $studentsCount }}</strong>
            </p>
        </div>

        <a href="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" 
           class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Exam Directory
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: SCHEDULE NEW EXAM PAPER FORM --}}
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-calendar-plus text-amber-400"></i>
                        <h3 class="text-sm font-bold text-white">Schedule New Paper</h3>
                    </div>
                    <span class="text-[10px] font-mono bg-indigo-950 text-indigo-300 border border-indigo-800/60 px-2.5 py-0.5 rounded-full font-bold">
                        {{ $targetName }}
                    </span>
                </div>

                <form method="POST" action="{{ Route::has('exams.store') ? route('exams.store') : url('/exams') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Pass class_id for Stream OR grade_name for Grade Level --}}
                    @if($classId)
                        <input type="hidden" name="class_id" value="{{ $classId }}">
                    @else
                        <input type="hidden" name="grade_name" value="{{ $id }}">
                    @endif

                    {{-- Exam Paper Title --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Exam / Paper Title <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="exam_name" required placeholder="e.g. Paper 1 (Theory), Paper 2 (Practical)"
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Academic Session --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Academic Session <span class="text-rose-500">*</span>
                        </label>
                        <select name="term_id" required 
                                class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($terms as $t)
                                <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                                    {{ $t->term_name }} ({{ $t->academic_year }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TARGET ALLOCATED SUBJECT SELECTOR --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Subject (Class Allocated) <span class="text-rose-500">*</span>
                        </label>
                        <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Subject --</option>
                            @forelse($allocatedSubjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</option>
                            @empty
                                <option value="" disabled>No subjects allocated to {{ $targetName }} yet</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Scheduled Date --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Scheduled Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="exam_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Exam Paper Schedule
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT COLUMN: SCHEDULED PAPERS LEDGER (COLLAPSIBLE BY SUBJECT) --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-layer-group text-amber-400 mr-2.5"></i> Scheduled Papers Ledger
                    </h3>
                    <span class="text-[10px] font-mono bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-0.5 rounded-full font-bold">
                        {{ $scheduledExams->count() }} {{ Str::plural('Paper', $scheduledExams->count()) }} Total
                    </span>
                </div>

                @if($scheduledExams->isEmpty())
                    <div class="p-12 text-center text-slate-400 italic">
                        <i class="fa-solid fa-folder-open text-3xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                        No exam papers scheduled for {{ $targetName }} in {{ $selectedTerm->term_name ?? 'this term' }}. Use the left form to schedule a paper.
                    </div>
                @else
                    @php
                        $groupedExams = $scheduledExams->groupBy(function($exam) {
                            if ($exam->subject) {
                                return $exam->subject->subject_name . ' (' . $exam->subject->subject_code . ')';
                            }
                            return 'Unassigned / General Subject';
                        });
                    @endphp

                    <div class="p-5 space-y-4">
                        @foreach($groupedExams as $subjectTitle => $papers)
                            @php
                                $firstExam = $papers->first();
                                $subjectId = $firstExam && $firstExam->subject ? $firstExam->subject->id : null;
                            @endphp

                            <div x-data="{ isOpen: true }" 
                                 class="border border-slate-200/90 dark:border-slate-700/70 rounded-2xl overflow-hidden bg-slate-50/50 dark:bg-slate-900/40 shadow-sm transition-all">
                                
                                {{-- Subject Accordion Header --}}
                                <button @click="isOpen = !isOpen" type="button" 
                                        class="w-full px-5 py-3.5 bg-white dark:bg-slate-800 hover:bg-slate-100/80 dark:hover:bg-slate-750 flex items-center justify-between text-left transition-colors select-none">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            <i class="fa-solid fa-book-open"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">{{ $subjectTitle }}</h4>
                                            <span class="text-[10px] font-medium text-slate-400 block mt-0.5">
                                                {{ $papers->count() }} {{ Str::plural('Paper', $papers->count()) }} Scheduled
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2.5">
                                        {{-- Subject Overview Analytics Link Button --}}
                                        @if($subjectId)
                                            <a href="{{ Route::has('exams.subject_report') ? route('exams.subject_report', ['subject_id' => $subjectId, 'term_id' => $selectedTerm->id ?? null]) : url('/exams/subject-report/' . $subjectId . '?term_id=' . ($selectedTerm->id ?? '')) }}" 
                                               @click.stop
                                               class="inline-flex items-center px-2.5 py-1 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold shadow-sm transition-all"
                                               title="View Subject Overview & Performance Analytics">
                                                <i class="fa-solid fa-chart-line mr-1.5"></i> Subject Overview
                                            </a>
                                        @endif

                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                            {{ $papers->count() }} {{ Str::plural('Paper', $papers->count()) }}
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
                                    </div>
                                </button>

                                {{-- Collapsible Papers Table --}}
                                <div x-show="isOpen" x-collapse class="border-t border-slate-200/70 dark:border-slate-700/60 overflow-x-auto bg-white dark:bg-slate-800">
                                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                                        <thead class="bg-slate-50 dark:bg-slate-850 text-slate-400 uppercase text-[9px] font-extrabold tracking-wider">
                                            <tr>
                                                <th scope="col" class="px-5 py-3 text-left">Paper Title</th>
                                                <th scope="col" class="px-5 py-3 text-left">Date</th>
                                                <th scope="col" class="px-5 py-3 text-left">Status</th>
                                                <th scope="col" class="px-5 py-3 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                                            @foreach($papers as $exam)
                                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                                        <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $exam->exam_name }}</strong>
                                                        <span class="text-[10px] font-mono text-slate-400">ID: #{{ $exam->id }}</span>
                                                    </td>

                                                    <td class="px-5 py-3.5 whitespace-nowrap font-mono text-slate-600 dark:text-slate-300">
                                                        <i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i>
                                                        {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}
                                                    </td>

                                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                                        @if($exam->status === 'published')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                                                <i class="fa-solid fa-check-circle mr-1"></i> Published
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                                                <i class="fa-solid fa-clock mr-1"></i> Pending Marks
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-5 py-3.5 whitespace-nowrap text-center space-x-1.5">
                                                        {{-- Enter Marks --}}
                                                        <a href="{{ route('exams.create_marks', ['exam_id' => $exam->id, 'grade' => $id]) }}" 
                                                           class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white transition-colors inline-flex items-center" title="Record Student Marks">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        {{-- View Report --}}
                                                        <a href="{{ route('exams.report', ['exam_id' => $exam->id, 'grade' => $id]) }}" 
                                                           class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-colors inline-flex items-center" title="Performance Report">
                                                            <i class="fa-solid fa-chart-column"></i>
                                                        </a>

                                                        {{-- Biometric Verification --}}
                                                        <a href="{{ url('/exams/' . $exam->id . '/verify') }}" 
                                                           class="p-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white transition-colors inline-flex items-center" title="Gatekeeper Biometric Terminal">
                                                            <i class="fa-solid fa-camera"></i>
                                                        </a>

                                                        {{-- Toggle Publish --}}
                                                        <form action="{{ url('/exams/' . $exam->id . '/toggle-publish') }}" method="POST" class="inline-block">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="p-1.5 rounded-lg {{ $exam->status === 'published' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} hover:opacity-80 transition-opacity" title="Toggle Publish">
                                                                <i class="fa-solid {{ $exam->status === 'published' ? 'fa-eye-slash' : 'fa-globe' }}"></i>
                                                            </button>
                                                        </form>

                                                        {{-- Delete --}}
                                                        <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Delete this scheduled exam paper?');" class="inline-block">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors" title="Delete Schedule">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection