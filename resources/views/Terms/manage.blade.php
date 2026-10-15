{{-- resources/views/terms/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Manage Terms | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Academic Terms</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage school semesters and dates</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Session Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
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
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: CREATE TERM FORM --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-calendar-plus text-indigo-500 mr-2.5"></i> Create New Term
                        </h3>
                    </div>

                    <form method="POST" action="{{ Route::has('terms.store') ? route('terms.store') : url('/terms') }}" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Term Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-bookmark"></i>
                                </div>
                                <select name="term_name" required
                                        class="w-full pl-10 pr-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Academic Year <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <input type="text" name="academic_year" value="{{ date('Y') }}" placeholder="e.g. 2026" required
                                       class="w-full pl-10 pr-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Start Date <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <input type="date" name="start_date" required
                                       class="w-full pl-10 pr-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                End Date <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-xl">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                </div>
                                <input type="date" name="end_date" required
                                       class="w-full pl-10 pr-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2.5"></i> Term History & Management
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Term Detail</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Academic Year</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Duration</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($terms as $term)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong class="text-sm font-bold text-slate-800 dark:text-slate-100 block">{{ $term->term_name }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-600">
                                            {{ $term->academic_year }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold text-[11px]">
                                                <i class="fa-solid fa-circle-play mr-1.5 text-[10px]"></i>
                                                <span>{{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex items-center text-rose-600 dark:text-rose-400 font-semibold text-[11px]">
                                                <i class="fa-solid fa-circle-stop mr-1.5 text-[10px]"></i>
                                                <span>{{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($term->is_current)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-emerald-500 text-white shadow-sm">
                                                <i class="fa-solid fa-circle-check mr-1 text-[11px]"></i> CURRENT
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-slate-100 dark:bg-slate-700/80 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                INACTIVE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if(!$term->is_current)
                                            <form action="{{ Route::has('terms.activate') ? route('terms.activate', $term->id) : url('/terms/' . $term->id . '/activate') }}" 
                                                  method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Set {{ $term->term_name }} ({{ $term->academic_year }}) as the active current term?');"
                                                        class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white dark:text-amber-400 dark:hover:text-white border border-amber-500/30 text-xs font-bold transition-all shadow-sm">
                                                    <i class="fa-solid fa-toggle-on mr-1.5"></i> SET ACTIVE
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-400 text-xs font-bold cursor-not-allowed border border-slate-200/50 dark:border-slate-700/50">
                                                <i class="fa-solid fa-lock mr-1.5"></i> ACTIVE TERM
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
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