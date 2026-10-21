{{-- resources/views/students/promote.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Mass Academic Promotion | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Mass Academic Promotion</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Batch transition student cohorts across Grade Levels for the new academic year</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Student Directory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span class="text-xs font-semibold">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Guidance Banner --}}
        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/50 flex items-start space-x-3 text-xs">
            <i class="fa-solid fa-circle-info text-indigo-600 dark:text-indigo-400 text-lg mt-0.5 shrink-0"></i>
            <div>
                <h4 class="font-bold text-indigo-950 dark:text-indigo-200">Grade Level Promotion Architecture</h4>
                    <p class="text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                    Students are selected from the active term{{ isset($currentTerm) && $currentTerm ? ' (' . $currentTerm->display_name . ')' : '' }}. Map each grade level to its destination grade, then choose the term they move into. Select <strong>Graduated / Alumni</strong> for final-year cohorts.
                </p>
            </div>
        </div>

        <form id="massPromotionForm" action="{{ Route::has('students.promote.mass') ? route('students.promote.mass') : url('/students/promote/mass') }}" method="POST" class="space-y-6">
            @csrf

            {{-- 1. Target Academic Term --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-calendar-days text-indigo-500 mr-2.5"></i> 1. Target Destination Academic Term
                    </h3>
                </div>
                <div class="p-6">
                    <label for="target_term_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Select Academic Term Students Are Moving Into <span class="text-rose-500">*</span>
                    </label>
                    <select name="target_term_id" id="target_term_id" required
                            class="w-full md:w-1/2 px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ isset($targetTerm) && $targetTerm?->id === $term->id ? 'selected' : '' }}>
                                {{ $term->term_name }} ({{ $term->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 2. Grade Level Transition Mapping Matrix --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-layer-group text-indigo-500 mr-2.5"></i> 2. Grade Level Mapping Matrix
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">Include</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Current Grade Level (From)</th>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-20">Transition</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Destination Grade Level (To)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                            @foreach($gradeLevels as $index => $grade)
                            @php
                                $totalEnrolled = $grade->students->count() ?? $grade->schoolClasses->flatMap(fn($c) => $c->students)->count();
                                // Pre-calculate next sequential grade level as default suggestion
                                $nextGrade = $gradeLevels->get($index + 1);
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="promote[{{ $grade->id }}][active]" value="1" checked
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-700 focus:ring-indigo-500">
                                </td>
                                
                                {{-- Current Grade Level --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="promote[{{ $grade->id }}][from_grade_level_id]" value="{{ $grade->id }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs font-mono shrink-0">
                                            #{{ $grade->level_order }}
                                        </div>
                                        <div>
                                            <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $grade->name }}</strong>
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                {{ $grade->schoolClasses->count() }} {{ Str::plural('stream', $grade->schoolClasses->count()) }} | {{ $totalEnrolled }} enrolled
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Arrow --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto">
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </div>
                                </td>

                                {{-- Target Grade Level Selection --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="promote[{{ $grade->id }}][to_grade_level_id]" 
                                            class="w-full max-w-xs px-3.5 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                                        <option value="">-- Skip / No Transition --</option>
                                        @foreach($gradeLevels as $destGrade)
                                            <option value="{{ $destGrade->id }}" {{ ($nextGrade && $nextGrade->id == $destGrade->id) ? 'selected' : '' }}>
                                                {{ $destGrade->name }}
                                            </option>
                                        @endforeach
                                        <option value="Graduated" class="text-rose-600 font-bold">🎓 Graduated / Alumni</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Submit Footer --}}
                <div class="p-6 border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i> All active students in included grade levels will be batch transitioned.
                    </p>
                    <button type="submit" 
                            onclick="return confirm('You are about to execute batch student promotions system-wide by Grade Level. Proceed?')"
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-rocket mr-2"></i> Process Grade Promotions
                    </button>
                </div>
            </div>

        </form>

    </div>
@endsection
