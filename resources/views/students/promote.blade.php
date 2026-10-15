{{-- resources/views/students/promote.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Mass Academic Promotion | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Mass Academic Promotion</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Batch transition students to target grades for the new academic year</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
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

        {{-- Logic Info Banner --}}
        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/50 flex items-start space-x-3 text-xs">
            <i class="fa-solid fa-circle-info text-indigo-600 dark:text-indigo-400 text-lg mt-0.5 shrink-0"></i>
            <div>
                <h4 class="font-bold text-indigo-950 dark:text-indigo-200">Batch Promotion Guidance</h4>
                <p class="text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                    Map each existing class level precisely to its new target destination. Active student accounts will be batch migrated in a single pass. Select <strong>Graduated / Alumni</strong> for final-year streams.
                </p>
            </div>
        </div>

        <form id="massPromotionForm" action="{{ route('students.promote.mass') }}" method="POST" class="space-y-6">
            @csrf

            {{-- 1. Target Academic Term --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-calendar-days text-indigo-500 mr-2.5"></i> 1. Target Destination Academic Term
                    </h3>
                </div>
                <div class="p-6">
                    <label for="target_term_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Select Academic Term Students Are Moving Into:
                    </label>
                    <select name="target_term_id" id="target_term_id" required
                            class="w-full md:w-1/2 px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                {{ $term->term_name }} ({{ $term->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 2. Class Transition Mapping --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-arrows-rotate text-indigo-500 mr-2.5"></i> 2. Class Level Mapping Matrix
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Current Class (From)</th>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Transition</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Destination Class (To)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                            @foreach($classes as $class)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="promote[{{ $class->id }}][active]" value="1" checked
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-700 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="promote[{{ $class->id }}][from_grade]" value="{{ $class->class_name }}">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                        {{ $class->class_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto">
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="promote[{{ $class->id }}][to_grade]" 
                                            class="w-full max-w-xs px-3.5 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Skip / No Change --</option>
                                        @foreach($classes as $destClass)
                                            <option value="{{ $destClass->class_name }}">{{ $destClass->class_name }}</option>
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
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i> Verify target fee structures before completing transition.
                    </p>
                    <button type="submit" 
                            onclick="return confirm('You are about to execute batch student promotions system-wide. Proceed?')"
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-rocket mr-2"></i> Process All Promotions
                    </button>
                </div>
            </div>

        </form>

    </div>
@endsection