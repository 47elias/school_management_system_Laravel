{{-- resources/views/teacher/exams/schedule.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Add an Exam')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-indigo-600 dark:text-indigo-400"></i>
                <span>Add an Exam</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure assessment details, max score, and target class allocation</p>
        </div>

        <a href="{{ route('teacher.exams.index') }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-xs">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Exams
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Selected Term Lock Banner --}}
        <div class="p-4 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 text-indigo-900 dark:text-indigo-200 flex items-center space-x-3 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 text-sm">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider">Locked Academic Context</h4>
                <p class="text-xs font-medium mt-0.5">
                    This assessment paper will be scheduled under: <strong class="underline">{{ $selectedTerm->term_name ?? 'Current Term' }} ({{ $selectedTerm->academic_year ?? date('Y') }})</strong>
                </p>
            </div>
        </div>

        {{-- Main Form Card --}}
        <form action="{{ route('teacher.exams.store') }}" method="POST">
            @csrf
            <input type="hidden" name="term_id" value="{{ $selectedTerm->id ?? 1 }}">

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/90 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center space-x-2">
                        <i class="fa-solid fa-sliders text-indigo-500"></i>
                        <span>Assessment Configuration</span>
                    </h3>
                </div>

                <div class="p-6 space-y-6">

                    {{-- Term Display (Read Only) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Academic Term</label>
                        <input type="text" 
                               value="{{ $selectedTerm->term_name ?? 'Current Academic Term' }}" 
                               readonly 
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold cursor-not-allowed">
                    </div>

                    {{-- Class & Subject Selection --}}
                    <div>
                        <label for="subject_assignment_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Choose Subject and Class <span class="text-rose-500">*</span>
                        </label>
                        <select name="subject_assignment_id" 
                                id="subject_assignment_id" 
                                required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose your subject and class --</option>
                            @foreach($myAssignments ?? [] as $assignment)
                                <option value="{{ $assignment->id }}" {{ old('subject_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                    {{ $assignment->subject->subject_name ?? $assignment->subject->name ?? 'N/A' }} — {{ $assignment->class_label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Choose the subject and class for this exam.</p>
                        @error('subject_assignment_id')
                            <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Exam Name --}}
                    <div>
                        <label for="exam_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Exam Title / Assessment Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="exam_name" 
                               id="exam_name" 
                               value="{{ old('exam_name') }}" 
                               placeholder="e.g. End of Term Examination, Class Test 1..." 
                               required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('exam_name')
                            <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Scheduled Date & Max Marks Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="exam_date" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Scheduled Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="exam_date" 
                                   id="exam_date" 
                                   value="{{ old('exam_date', date('Y-m-d')) }}" 
                                   required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('exam_date')
                                <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_marks" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Max Marks (Weight) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   name="max_marks" 
                                   id="max_marks" 
                                   value="{{ old('max_marks', 100) }}" 
                                   min="1" 
                                   step="0.01" 
                                   required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-bold">
                            @error('max_marks')
                                <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>

                {{-- Footer Actions --}}
                <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90 flex items-center justify-between">
                    <a href="{{ route('teacher.exams.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>

                    <button type="submit" 
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xs flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Exam</span>
                    </button>
                </div>

            </div>
        </form>

    </div>
@endsection
