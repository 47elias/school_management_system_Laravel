{{-- resources/views/expenses/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Expense Report | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Expense Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Control & track school spending and operational costs</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-print mr-2"></i> PRINT REPORT
            </button>
            <a href="{{ Route::has('expenses.create') ? route('expenses.create') : url('/expenses/create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> ADD NEW EXPENSE
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Printable Report Header --}}
        <div class="hidden print:block text-center border-b-2 border-slate-900 pb-4 mb-6">
            <h2 class="text-2xl font-black uppercase text-slate-900">{{ env('SCHOOL_NAME', 'SCHOOL MANAGEMENT SYSTEM') }}</h2>
            <h4 class="text-base font-bold text-slate-700 mt-1">Official Expenditure Report</h4>
            <p class="text-xs text-slate-500 font-mono mt-0.5">Generated: {{ date('D, d M Y H:i') }}</p>
        </div>

        {{-- Session Messages --}}
        @if(session('success'))
            <div class="print:hidden p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Financial Summary Card --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 print:grid-cols-1">
            <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-6 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Total Expenses (This Page)</span>
                    <h3 class="text-3xl font-black mt-2 font-mono tracking-tight">${{ number_format($expenses->sum('amount'), 2) }}</h3>
                </div>
                <i class="fa-solid fa-file-invoice-dollar absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>
        </div>

        {{-- Expenditure Logs Directory --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors print:border-none print:shadow-none print:bg-transparent">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 print:hidden">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-receipt text-rose-500 mr-2.5"></i> Expenditure Logs
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-900 text-white dark:bg-slate-950">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Ref No.</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold uppercase tracking-wider print:hidden">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60 print:divide-slate-300">
                        @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-medium text-slate-700 dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $expense->description }}</strong>
                                @if($expense->notes)
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block leading-tight">{{ $expense->notes }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs px-2 py-1 rounded bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300">
                                    {{ $expense->reference_no ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-rose-600 dark:text-rose-400 text-sm">
                                ${{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center print:hidden">
                                <form action="{{ Route::has('expenses.destroy') ? route('expenses.destroy', $expense->id) : url('/expenses/' . $expense->id) }}" 
                                      method="POST" onsubmit="return confirm('Delete record?')" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Delete Expense">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                No expense records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700/60 print:hidden">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection