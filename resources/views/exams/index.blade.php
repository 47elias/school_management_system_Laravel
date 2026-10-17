{{-- resources/views/exams/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Exam Management | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div x-data="{ showScheduleModal: false }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Exam Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Schedule examination papers and manage grade entries</p>
        </div>

        <div class="flex items-center space-x-3">
            <button @click="showScheduleModal = true" 
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-calendar-plus mr-2"></i> SCHEDULE EXAM PAPER
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ showScheduleModal: false, search: '' }" class="space-y-6">

        {{-- Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Filters & Directory Header --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-file-pen text-indigo-500 text-lg"></i>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Scheduled Exams Directory</h3>
                </div>

                {{-- Term Selector Filter --}}
                <form method="GET" action="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" class="flex items-center space-x-2">
                    <label for="term_id" class="text-xs font-bold uppercase text-slate-400">Academic Term:</label>
                    <select name="term_id" id="term_id" onchange="this.form.submit()" 
                            class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                                {{ $t->term_name }} ({{ $t->academic_year }}) {{ $t->is_current ? '— [Current]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Exam / Paper Title</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mark Entry by Grade</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($exams as $exam)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $exam->exam_name }}</strong>
                                <span class="text-[10px] text-slate-400">ID: #{{ $exam->id }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                    {{ $exam->subject->subject_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-600 dark:text-slate-300">
                                <i class="fa-regular fa-calendar text-slate-400 mr-1.5"></i>
                                {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($exam->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        Pending Marks
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end flex-wrap gap-1">
                                    @foreach($grades as $g)
                                        <a href="{{ Route::has('exams.createMarks') ? route('exams.createMarks', ['exam_id' => $exam->id, 'grade' => $g]) : url('/exams/' . $exam->id . '/marks/' . $g) }}" 
                                           class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-200 font-bold text-[10px] transition-colors border border-slate-200 dark:border-slate-600"
                                           title="Record marks for {{ $g }}">
                                            {{ $g }}
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ Route::has('exams.destroy') ? route('exams.destroy', $exam->id) : url('/exams/' . $exam->id) }}" 
                                      method="POST" onsubmit="return confirm('Delete this exam schedule and all recorded marks?');" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Delete Exam">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-xmark text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                No exams scheduled for {{ $selectedTerm->term_name ?? 'this term' }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Schedule Exam Modal --}}
        <div x-show="showScheduleModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showScheduleModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                    <form action="{{ Route::has('exams.store') ? route('exams.store') : url('/exams') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                <i class="fa-solid fa-calendar-plus text-indigo-500 mr-2"></i> Schedule New Exam Paper
                            </h3>
                            <button type="button" @click="showScheduleModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Exam / Paper Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="exam_name" required placeholder="e.g. Paper 1 (Theory), Paper 2 (Practical)"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Academic Term <span class="text-rose-500">*</span>
                                </label>
                                <select name="term_id" required 
                                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ $t->is_current ? 'selected' : '' }}>
                                            {{ $t->term_name }} ({{ $t->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Subject <span class="text-rose-500">*</span>
                                </label>
                                <select name="subject_id" required 
                                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Exam Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="exam_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="showScheduleModal = false" 
                                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">
                                Save Exam Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection