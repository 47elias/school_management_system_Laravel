{{-- resources/views/subjects/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Curriculum Dashboard | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Academic Subject Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Overview of subjects, streams, subject combinations, and faculty allocations</p>
        </div>

        {{-- Top Navigation Shortcuts --}}
        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ Route::has('subjects.manage') ? route('subjects.manage') : url('/subjects/manage') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-sliders mr-2"></i> Manage Subjects & Combinations
            </a>
            <a href="{{ Route::has('subjects.allocate_classes') ? route('subjects.allocate_classes') : url('/subjects/allocate-classes') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-sitemap mr-2"></i> Allocate Classes
            </a>
            <a href="{{ Route::has('subjects.allocate_teachers') ? route('subjects.allocate_teachers') : url('/subjects/allocate-teachers') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-chalkboard-user mr-2"></i> Allocate Teachers
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Session Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        {{-- METRICS OVERVIEW CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Subjects</span>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-mono">{{ $subjects->count() }}</h3>
                    <span class="text-[11px] text-indigo-500 font-semibold mt-1 inline-block">Active Curriculum Catalog</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-500 text-xl font-bold">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Class Streams</span>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-mono">{{ isset($schoolClasses) ? $schoolClasses->count() : (isset($classes) ? $classes->count() : 0) }}</h3>
                    <span class="text-[11px] text-emerald-500 font-semibold mt-1 inline-block">Configured Streams</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 text-xl font-bold">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Subject Combinations</span>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-mono">{{ isset($combinations) ? $combinations->count() : 0 }}</h3>
                    <span class="text-[11px] text-purple-500 font-semibold mt-1 inline-block">Senior/Stream Packages</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-500 text-xl font-bold">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Teaching Load Assignments</span>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1 font-mono">{{ $assignments->count() }}</h3>
                    <span class="text-[11px] text-amber-500 font-semibold mt-1 inline-block">Faculty Allocations</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 text-xl font-bold">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </div>

        {{-- MAIN DASHBOARD SECTIONS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- SUBJECT CATALOG SUMMARY --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-book-bookmark text-indigo-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Defined Subject Catalog</h3>
                    </div>
                    <a href="{{ Route::has('subjects.manage') ? route('subjects.manage') : url('/subjects/manage') }}" class="text-[11px] text-indigo-300 font-bold hover:underline">Manage All &rarr;</a>
                </div>

                <div class="w-full">
                    <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs table-fixed">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px] bg-slate-50/50 dark:bg-slate-900/50">
                                <th scope="col" class="px-4 py-3 text-left w-1/4">Code</th>
                                <th scope="col" class="px-4 py-3 text-left w-1/2">Subject Title</th>
                                <th scope="col" class="px-3 py-3 text-center w-1/4">Classification</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($subjects->take(6) as $subject)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700 inline-block">
                                            {{ $subject->subject_code }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                        {{ $subject->subject_name }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $subject->type === 'Core' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/40' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40' }}">
                                            {{ $subject->type }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-400">No subjects defined yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SUBJECT COMBINATIONS SUMMARY --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-layer-group text-purple-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Subject Combinations & Packages</h3>
                    </div>
                    <a href="{{ Route::has('subjects.manage') ? route('subjects.manage') : url('/subjects/manage') }}" class="text-[11px] text-purple-300 font-bold hover:underline">Configure &rarr;</a>
                </div>

                <div class="w-full">
                    <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs table-fixed">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px] bg-slate-50/50 dark:bg-slate-900/50">
                                <th scope="col" class="px-4 py-3 text-left w-1/3">Combination Name</th>
                                <th scope="col" class="px-3 py-3 text-center w-1/4">Target Scope</th>
                                <th scope="col" class="px-4 py-3 text-left w-2/5">Included Subjects</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @if(isset($combinations) && $combinations->count() > 0)
                                @foreach($combinations->take(6) as $comb)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                            {{ $comb->name }}
                                            <span class="text-[10px] font-mono text-slate-400 block font-normal">({{ $comb->code ?? 'N/A' }})</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40 inline-block truncate max-w-full">
                                                {{ $comb->grade ?? 'All Forms' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 break-words text-[11px]">
                                            @if(isset($comb->subjects) && count($comb->subjects) > 0)
                                                {{ collect($comb->subjects)->pluck('subject_name')->implode(', ') }}
                                            @else
                                                <span class="text-slate-400 italic">No subjects mapped</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-400">
                                        No subject combinations created yet.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
@endsection