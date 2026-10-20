{{-- resources/views/fees/report.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Financial Balance Report | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Financial Balance Report</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Comprehensive overview of revenue collections, term expenses, and student account balances</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2.5">
            <a href="{{ Route::has('expenses.index') ? route('expenses.index') : url('/expenses') }}" 
               class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-circle-minus mr-2"></i> Record Expense
            </a>
            <button onclick="exportToExcel()" 
                    class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-file-excel mr-2"></i> Export Excel
            </button>
            <button onclick="window.print()" 
                    class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-print mr-2"></i> Print Statement
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Printable Header --}}
    <div class="hidden print:block text-center border-b-2 border-slate-900 pb-4 mb-6">
        <h2 class="text-2xl font-black uppercase text-slate-900">{{ env('SCHOOL_NAME', 'Mukahlera High School') }}</h2>
        <h4 class="text-base font-bold uppercase tracking-widest text-slate-700 mt-1">Financial Balance & Arrears Audit Report</h4>
        <p class="text-xs text-slate-600 mt-1">
            <strong>Target Term:</strong> {{ $currentTerm->term_name ?? 'Active Term' }} | <strong>Generated:</strong> {{ date('d/m/Y H:i') }}
        </p>
    </div>

    <div class="space-y-6">
        {{-- Top Info KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 no-print">
            {{-- Gross Collections --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm relative overflow-hidden transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Gross Collections</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">${{ number_format($totalRevenue ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-700/60 pt-2">All-time revenue received</p>
            </div>

            {{-- Net Available Funds --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm relative overflow-hidden transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Net Treasury Funds</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">${{ number_format($schoolBalance ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-700/60 pt-2">Actual balance after operational costs</p>
            </div>

            {{-- Term Expenses --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm relative overflow-hidden transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Term Expenditure</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">${{ number_format($currentTermExpenses ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-700/60 pt-2">Salaries & costs for active term</p>
            </div>

            {{-- Today's Income --}}
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm relative overflow-hidden transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Today's Receipts</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">${{ number_format($dailyIncome ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mt-3 border-t border-slate-100 dark:border-slate-700/60 pt-2">Direct payments recorded today</p>
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 no-print transition-colors">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider flex items-center mb-4">
                <i class="fa-solid fa-filter text-indigo-500 mr-2"></i> Report Filters & Search Options
            </h3>
            <form action="{{ Route::has('fees.report') ? route('fees.report') : url('/fees/report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                
                {{-- Search --}}
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Student Name / ID</label>
                    <input type="text" name="search" value="{{ $searchName ?? '' }}" placeholder="Search by name or number..."
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>

                {{-- Grade Filter --}}
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Grade Level / Class</label>
                    <select name="grade" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                        <option value="">All Grade Levels</option>
                        @if(isset($gradeLevels) && count($gradeLevels) > 0)
                            @foreach($gradeLevels as $gl)
                                <option value="{{ is_object($gl) ? $gl->name : $gl }}" {{ ($selectedGrade ?? '') == (is_object($gl) ? $gl->name : $gl) ? 'selected' : '' }}>
                                    {{ is_object($gl) ? $gl->name : 'Grade ' . $gl }}
                                </option>
                            @endforeach
                        @elseif(isset($grades))
                            @foreach($grades as $g)
                                <option value="{{ $g }}" {{ ($selectedGrade ?? '') == $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Term Selector --}}
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Academic Term</label>
                    <select name="term_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" {{ ($selectedTermId ?? ($currentTerm->id ?? null)) == $term->id ? 'selected' : '' }}>
                                {{ $term->term_name }} ({{ $term->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Account Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                        <option value="">All Accounts</option>
                        <option value="arrears" {{ ($status ?? '') == 'arrears' ? 'selected' : '' }}>Arrears Only</option>
                    </select>
                </div>

                {{-- Submit --}}
                <div class="lg:col-span-1">
                    <button type="submit" class="w-full h-[38px] inline-flex items-center justify-center px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-magnifying-glass mr-1.5 sm:hidden lg:inline-block"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Filtered Summary Metrics Bar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-slate-900 text-white p-5 rounded-3xl shadow-sm border border-slate-800">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Valid Invoiced</span>
                <h3 class="text-2xl font-black mt-1">${{ number_format($report->sum('expected'), 2) }}</h3>
            </div>
            <div class="bg-emerald-600 text-white p-5 rounded-3xl shadow-sm border border-emerald-500">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Total Collected</span>
                <h3 class="text-2xl font-black mt-1">${{ number_format($report->sum('paid'), 2) }}</h3>
            </div>
            <div class="bg-rose-600 text-white p-5 rounded-3xl shadow-sm border border-rose-500">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-100">Net Account Balance</span>
                <h3 class="text-2xl font-black mt-1">${{ number_format($report->sum('balance'), 2) }}</h3>
            </div>
            <div class="bg-sky-600 text-white p-5 rounded-3xl shadow-sm border border-sky-500">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-100">Total Term Dues to Clear</span>
                <h3 class="text-2xl font-black mt-1">${{ number_format($report->sum('term_dues'), 2) }}</h3>
            </div>
        </div>

        {{-- Main Financial Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table id="reportTable" class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Grade Level</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Invoiced</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Paid</th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Due to Clear</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Balance</th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        @forelse($report as $data)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ $data->student_number }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $data->surname }}, {{ $data->name }}</span>
                                @if(isset($data->term_id) && $data->term_id == ($selectedTermId ?? ($currentTerm->id ?? null)))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-sky-500 text-white ml-2 no-print">NEW ADMISSION</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                    {{ $data->grade }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-slate-700 dark:text-slate-300">${{ number_format($data->expected, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($data->paid, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($data->term_dues > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                        ${{ number_format($data->term_dues, 2) }} Due
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <i class="fa-solid fa-check mr-1 text-[10px]"></i> NO DUES
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $data->balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                ${{ number_format($data->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center no-print">
                                <a href="{{ route('fees.show', ['id' => $data->id, 'term_id' => $currentTerm->id]) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-bold border border-indigo-200 dark:border-indigo-800/50 transition-colors">
                                    STATEMENT
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                No student financial records found matching the specified filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold text-xs text-slate-800 dark:text-slate-100 border-t-2 border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right">GRAND TOTALS:</td>
                            <td class="px-6 py-4 text-right">${{ number_format($report->sum('expected'), 2) }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-400">${{ number_format($report->sum('paid'), 2) }}</td>
                            <td class="px-6 py-4 text-center text-rose-600 dark:text-rose-400">${{ number_format($report->sum('term_dues'), 2) }}</td>
                            <td class="px-6 py-4 text-right text-rose-600 dark:text-rose-400">${{ number_format($report->sum('balance'), 2) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            var table = document.getElementById("reportTable");
            var html = table.outerHTML;
            html = html.replace(/<i class=".*?"><\/i>/g, '');
            html = html.replace(/<th class="no-print.*?<\/th>/g, '');
            html = html.replace(/<td class="no-print.*?<\/td>/g, '');
            window.open('data:application/vnd.ms-excel,' + encodeURIComponent(html));
        }
    </script>
@endsection
