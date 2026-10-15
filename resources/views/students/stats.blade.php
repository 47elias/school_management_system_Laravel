{{-- resources/views/students/stats.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Enrollment Analytics | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Enrollment Analytics</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Academic demographics and enrollment distribution metrics</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-users mr-2"></i> Manage Students
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Total Students --}}
            <div class="p-6 rounded-2xl bg-indigo-600 text-white shadow-sm flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Total Students</p>
                    <h3 class="text-3xl font-extrabold">{{ $classStats->sum('total') }}</h3>
                </div>
                <i class="fa-solid fa-graduation-cap text-5xl opacity-20 absolute right-4 bottom-3"></i>
            </div>

            {{-- Active Term --}}
            <div class="p-6 rounded-2xl bg-emerald-600 text-white shadow-sm flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Active Term</p>
                    <h3 class="text-2xl font-extrabold truncate max-w-[180px]">{{ \App\Models\Term::where('is_current', true)->value('term_name') ?? 'None' }}</h3>
                </div>
                <i class="fa-solid fa-flag-checkered text-5xl opacity-20 absolute right-4 bottom-3"></i>
            </div>

            {{-- Class Groups --}}
            <div class="p-6 rounded-2xl bg-sky-600 text-white shadow-sm flex items-center justify-between relative overflow-hidden">
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Class Streams</p>
                    <h3 class="text-3xl font-extrabold">{{ $classStats->count() }}</h3>
                </div>
                <i class="fa-solid fa-school text-5xl opacity-20 absolute right-4 bottom-3"></i>
            </div>

            {{-- Gender Balance --}}
            <div class="p-6 rounded-2xl bg-amber-500 text-white shadow-sm flex items-center justify-between relative overflow-hidden">
                @php
                    $f = $genderStats->where('gender', 'Female')->first()->total ?? 0;
                    $m = $genderStats->where('gender', 'Male')->first()->total ?? 0;
                @endphp
                <div class="space-y-1">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Girls / Boys</p>
                    <h3 class="text-3xl font-extrabold">{{ $f }} / {{ $m }}</h3>
                </div>
                <i class="fa-solid fa-venus-mars text-5xl opacity-20 absolute right-4 bottom-3"></i>
            </div>

        </div>

        {{-- Detailed Analytics --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Main Grade Distribution --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-chart-column text-indigo-500 mr-2.5"></i> Enrollment by Grade Level
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                        <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Grade Level</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Distribution</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                            @php $maxVal = $classStats->max('total') ?: 1; @endphp
                            @foreach($classStats as $stat)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-3.5 text-xs font-bold text-slate-800 dark:text-slate-100">{{ $stat->grade }}</td>
                                <td class="px-6 py-3.5 w-1/2">
                                    <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2 rounded-full overflow-hidden">
                                        <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full transition-all duration-300"
                                             style="width: {{ ($stat->total / $maxVal) * 100 }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                        {{ $stat->total }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Side Breakdown Widgets --}}
            <div class="space-y-6">
                
                {{-- Term Progression --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-chart-line text-emerald-500 mr-2.5"></i> Term Growth Metrics
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($termStats as $t)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60">
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $t->term_name }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $t->academic_year }}</p>
                            </div>
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center">
                                <i class="fa-solid fa-arrow-trend-up mr-1 text-xs"></i> {{ $t->total }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Gender Ratio --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors p-6 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center border-b border-slate-100 dark:border-slate-700 pb-3">
                        <i class="fa-solid fa-pie-chart text-amber-500 mr-2.5"></i> Gender Ratio Balance
                    </h3>

                    @php $total = $classStats->sum('total') ?: 1; @endphp
                    @foreach($genderStats as $g)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700 dark:text-slate-200 flex items-center">
                                <i class="fa-solid {{ $g->gender == 'Female' ? 'fa-venus text-purple-500' : 'fa-mars text-sky-500' }} mr-2"></i>
                                {{ $g->gender }}
                            </span>
                            <span class="text-slate-500 dark:text-slate-400">{{ $g->total }} Students</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2 rounded-full overflow-hidden">
                            <div class="{{ $g->gender == 'Female' ? 'bg-purple-500' : 'bg-sky-500' }} h-full rounded-full"
                                 style="width: {{ ($g->total / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>

        </div>

    </div>
@endsection