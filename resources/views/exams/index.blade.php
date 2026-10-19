{{-- resources/views/exams/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Examination Hub | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Examination Hub</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Select a Grade Level or Class Stream to schedule exams and manage paper entry</p>
        </div>

        {{-- Academic Term Selector --}}
        <form method="GET" action="{{ Route::has('exams.index') ? route('exams.index') : url('/exams') }}" class="flex items-center space-x-2">
            <label for="term_id" class="text-xs font-bold uppercase tracking-wider text-slate-400">Academic Session:</label>
            <select name="term_id" id="term_id" onchange="this.form.submit()" 
                    class="px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                @foreach($terms as $t)
                    <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                        {{ $t->term_name }} ({{ $t->academic_year }}) {{ $t->is_current ? '— [Current]' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endsection

@section('content')
    <div x-data="{ searchQuery: '' }" class="space-y-6">

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

        {{-- Search Filter Bar --}}
        <div class="relative w-full">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" x-model="searchQuery" placeholder="Search Grade Level or Class Stream..." 
                   class="w-full pl-10 pr-4 py-3 text-xs rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm font-medium">
        </div>

        {{-- COLLAPSIBLE GRADE ACCORDION DIRECTORY --}}
        <div class="space-y-4">
            @forelse($gradeData as $index => $g)
                @php
                    $gName = $g['name'];
                    $streams = $g['streams'];
                    $allStreamNames = $streams->pluck('name')->implode(' ');
                    $searchHaystack = strtolower($gName . ' ' . $allStreamNames);
                @endphp

                <div x-data="{ isOpen: false }" 
                     x-show="!searchQuery || '{{ addslashes($searchHaystack) }}'.includes(searchQuery.toLowerCase().trim())"
                     class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                    
                    {{-- Grade Header --}}
                    <div @click="isOpen = !isOpen" class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none hover:bg-slate-850 transition-colors">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-bold text-xs font-mono shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>{{ $gName }}</span>
                                    <span class="text-[10px] font-mono font-bold bg-indigo-950 px-2.5 py-0.5 rounded-full text-indigo-300 border border-indigo-800/60">
                                        {{ $streams->count() }} {{ Str::plural('Stream', $streams->count()) }}
                                    </span>
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-solid fa-users mr-1 text-slate-500"></i> {{ $g['student_count'] }} Enrolled Students &bull; 
                                    <i class="fa-solid fa-file-pen mr-1 text-slate-500"></i> {{ $g['exams_count'] }} Scheduled Papers
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3" @click.stop>
                            {{-- Schedule Grade-Wide Exam Button --}}
                            <a href="{{ route('exams.schedule', ['type' => 'grade', 'id' => urlencode($gName)]) }}" 
                               class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm flex items-center">
                                <i class="fa-solid fa-calendar-plus mr-1.5"></i> Schedule Grade Exam
                            </a>

                            <div @click="isOpen = !isOpen" class="p-2 cursor-pointer text-slate-400 hover:text-white">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Collapsible Stream Breakout Body --}}
                    <div x-show="isOpen" x-collapse class="p-6 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/40 dark:bg-slate-900/30 space-y-4">
                        
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                            Class Streams & Assigned Curriculum
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($streams as $st)
                                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 flex flex-col justify-between space-y-4 shadow-sm">
                                    
                                    {{-- Stream Header Info --}}
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center">
                                                <i class="fa-solid fa-chalkboard text-indigo-500 mr-2"></i> {{ $st['name'] }}
                                            </h4>
                                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5 block">
                                                <i class="fa-solid fa-user-graduate mr-1 text-slate-400"></i> {{ $st['student_count'] }} Students in Class
                                            </span>
                                        </div>

                                        {{-- Schedule Exam Action Button --}}
                                        <a href="{{ route('exams.schedule', ['type' => 'stream', 'id' => $st['id']]) }}" 
                                           class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm flex items-center shrink-0">
                                            <i class="fa-solid fa-calendar-plus mr-1.5"></i> Schedule Exam
                                        </a>
                                    </div>

                                    {{-- NUMBER OF ALLOCATED SUBJECTS BADGE --}}
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Allocated Subjects:</span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                            <i class="fa-solid fa-book-open mr-1.5 text-xs"></i>
                                            {{ $st['subject_count'] }} {{ Str::plural('Subject', $st['subject_count']) }}
                                        </span>
                                    </div>

                                    {{-- Stream Exam Counter Footer --}}
                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between text-[10px] font-mono font-bold text-slate-400">
                                        <span>Scheduled Papers: <strong class="text-indigo-600 dark:text-indigo-400">{{ $st['exams_count'] }}</strong></span>
                                        <a href="{{ route('exams.schedule', ['type' => 'stream', 'id' => $st['id']]) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Manage Schedule &rarr;
                                        </a>
                                    </div>

                                </div>
                            @empty
                                <div class="col-span-2 py-6 text-center text-xs text-slate-400 italic bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                                    No class streams defined under {{ $gName }}.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 text-slate-400">
                    No Grade Levels or Classes found in system.
                </div>
            @endforelse
        </div>

    </div>
@endsection