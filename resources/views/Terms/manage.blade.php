{{-- resources/views/terms/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Academic Terms | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Academic Term Architecture</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage academic sessions, term timelines, and active session configuration</p>
        </div>

        @php
            $currentActiveTerm = $terms->firstWhere('is_current', true);
        @endphp

        <div class="flex items-center space-x-3">
            @if($currentActiveTerm)
                <div class="px-4 py-2 rounded-2xl bg-emerald-500/10 dark:bg-emerald-950/40 border border-emerald-500/30 flex items-center space-x-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 font-mono">
                        ACTIVE: {{ $currentActiveTerm->term_name }} ({{ $currentActiveTerm->academic_year }})
                    </span>
                </div>
            @else
                <div class="px-4 py-2 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs font-bold flex items-center space-x-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>NO ACTIVE TERM SET</span>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Flash Alerts --}}
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

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Error</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        {{-- Governance Notice Banner --}}
        <div class="p-4 rounded-2xl bg-slate-900 text-white shadow-sm border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 text-sm font-bold shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-100">Term Lifecycle & Integrity Rule</h4>
                    <p class="text-slate-400 mt-0.5">Concluded (past) academic terms are automatically locked and cannot be reactivated to safeguard historical billing ledgers and academic records.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10px] font-mono font-bold border border-slate-700">
                    Total Terms: {{ $terms->count() }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: CREATE TERM FORM --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                    
                    {{-- Header Banner --}}
                    <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-calendar-plus text-indigo-400"></i>
                            <h3 class="text-sm font-bold tracking-tight text-white">Create New Term</h3>
                        </div>
                        <span class="text-[10px] font-mono bg-indigo-950/80 text-indigo-300 border border-indigo-800/60 px-2 py-0.5 rounded-full font-bold">
                            Setup
                        </span>
                    </div>

                    <form method="POST" action="{{ Route::has('terms.store') ? route('terms.store') : url('/terms') }}" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Term Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-bookmark"></i>
                                </div>
                                <select name="term_name" required
                                        class="w-full pl-9 pr-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                </select>
                            </div>
                        </div>

                        {{-- Academic Year: Readonly Display --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                    Academic Year
                                </label>
                                <span class="text-[9px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                                    System Generated
                                </span>
                            </div>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-lock text-slate-400"></i>
                                </div>
                                <input type="text" name="academic_year" value="{{ date('Y') }}" readonly
                                       class="w-full pl-9 pr-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed focus:outline-none select-none" 
                                       title="Academic year is set automatically and cannot be modified manually.">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Start Date <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <input type="date" name="start_date" required
                                       class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                End Date <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                </div>
                                <input type="date" name="end_date" required
                                       class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Academic Term
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT COLUMN: TERM HISTORY & MANAGEMENT --}}
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                    
                    {{-- Header Banner --}}
                    <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i>
                            <h3 class="text-sm font-bold tracking-tight text-white">Term History & Activation Management</h3>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400">
                            Session Registry
                        </span>
                    </div>

                    {{-- Fit-to-Width Table --}}
                    <div class="w-full">
                        <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs table-fixed">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px] bg-slate-50/50 dark:bg-slate-900/50">
                                    <th scope="col" class="px-4 py-3 text-left w-1/4">Term Detail</th>
                                    <th scope="col" class="px-3 py-3 text-center w-1/6">Year</th>
                                    <th scope="col" class="px-4 py-3 text-left w-1/4">Duration</th>
                                    <th scope="col" class="px-3 py-3 text-center w-1/6">Status</th>
                                    <th scope="col" class="px-4 py-3 text-right w-1/6">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($terms as $term)
                                    @php
                                        $isPast = \Carbon\Carbon::parse($term->end_date)->endOfDay()->isPast();
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center space-x-2.5">
                                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-slate-700 dark:text-slate-300 shrink-0">
                                                    <i class="fa-solid fa-graduation-cap text-[11px] text-indigo-500"></i>
                                                </div>
                                                <div class="min-w-0 break-words">
                                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block leading-tight truncate">
                                                        {{ $term->term_name }}
                                                    </strong>
                                                    <span class="text-[9px] text-slate-400">ID: #{{ $term->id }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3.5 text-center">
                                            <span class="font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-900 px-2.5 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700 inline-block">
                                                {{ $term->academic_year }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3.5">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold text-[10px]">
                                                    <i class="fa-solid fa-circle-play mr-1 text-[8px] shrink-0"></i>
                                                    <span class="truncate">{{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}</span>
                                                </div>
                                                <div class="flex items-center text-rose-600 dark:text-rose-400 font-semibold text-[10px]">
                                                    <i class="fa-solid fa-circle-stop mr-1 text-[8px] shrink-0"></i>
                                                    <span class="truncate">{{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3.5 text-center">
                                            @if($term->is_current)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-mono">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> ACTIVE
                                                </span>
                                            @elseif($isPast)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 font-mono">
                                                    <i class="fa-solid fa-box-archive mr-1 text-[8px]"></i> PAST
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 font-mono">
                                                    <i class="fa-solid fa-clock mr-1 text-[8px]"></i> NEXT
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3.5 text-right">
                                            @if($term->is_current)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-900 text-slate-400 text-[10px] font-bold border border-slate-200 dark:border-slate-700 cursor-not-allowed">
                                                    <i class="fa-solid fa-lock mr-1 text-[9px]"></i> ACTIVE
                                                </span>
                                            @elseif($isPast)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-bold border border-rose-500/20 cursor-not-allowed" 
                                                      title="Past terms are locked.">
                                                    <i class="fa-solid fa-ban mr-1 text-[9px]"></i> LOCKED
                                                </span>
                                            @else
                                                <form action="{{ Route::has('terms.activate') ? route('terms.activate', $term->id) : url('/terms/' . $term->id . '/activate') }}" 
                                                      method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Set {{ $term->term_name }} ({{ $term->academic_year }}) as active term?');"
                                                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold transition-all shadow-sm">
                                                        <i class="fa-solid fa-toggle-on mr-1 text-[9px]"></i> ACTIVATE
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                                <i class="fa-solid fa-calendar-xmark text-xl"></i>
                                            </div>
                                            <h4 class="font-bold text-slate-700 dark:text-slate-300">No Academic Terms Found</h4>
                                            <p class="text-xs text-slate-400 mt-1">Use the form on the left to create the first term.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection