{{-- resources/views/fees/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Payment History | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Payment History</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Audit log of all student fee collection transactions</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('fees.create') ? route('fees.create') : url('/fees/create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> Collect New Fee
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            {{-- Header Actions --}}
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2.5"></i> Recent Payment Transactions
                </h3>

                <form action="{{ Route::has('fees.index') ? route('fees.index') : url('/fees') }}" method="GET" class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Search student or ref no..." value="{{ request('search') }}"
                               class="pl-9 pr-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64 font-medium">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    <button type="submit" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs transition-colors">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ Route::has('fees.index') ? route('fees.index') : url('/fees') }}" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 text-xs hover:bg-rose-100 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Term</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Method</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reference</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Remarks</th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-slate-900 dark:text-white block">{{ $payment->student->surname }}, {{ $payment->student->name }}</strong>
                                <span class="text-[11px] font-mono text-slate-400">{{ $payment->student->student_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-medium whitespace-nowrap">
                                {{ $payment->term->term_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                    {{ $payment->payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-800/50">
                                    {{ $payment->reference_no ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm whitespace-nowrap">
                                ${{ number_format($payment->amount_paid, 2) }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                {{ $payment->remarks ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <form action="{{ Route::has('fees.destroy') ? route('fees.destroy', $payment->id) : url('/fees/' . $payment->id) }}" method="POST" 
                                      onsubmit="return confirm('WARNING: Are you sure you want to delete this payment record? Balance will automatically recalculate.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors" title="Delete Payment">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <i class="fa-solid fa-inbox text-xl"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 dark:text-slate-300">No Records Found</h4>
                                <p class="text-xs text-slate-400 mt-1">No payment transactions match your query.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($payments, 'hasPages') && $payments->hasPages())
                <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection