@extends('layouts.student')

@section('content')
@php
    $sortedTerms = $allTerms->sortByDesc('is_current')->values();

    $requestedTermId = request('term_id');
    if ($requestedTermId) {
        $displayTerm = $sortedTerms->firstWhere('id', $requestedTermId);
    } else {
        $displayTerm = $sortedTerms->first();
    }

    $pastTransactions = \App\Models\Payment::where('student_id', $student->id)
        ->whereHas('term', function($q) use ($displayTerm) {
            $q->where('start_date', '<', $displayTerm->start_date);
        })->get();

    $carriedForward = 0;
    foreach($pastTransactions as $pt) {
        if ($pt->amount_paid < 0) {
            $carriedForward += abs($pt->amount_paid); 
        } else {
            $carriedForward -= $pt->amount_paid;
        }
    }

    $termTransactions = \App\Models\Payment::where('student_id', $student->id)
        ->where('term_id', $displayTerm->id)
        ->orderBy('payment_date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    $invoices = $termTransactions->where('amount_paid', '<', 0);
    $receipts = $termTransactions->where('amount_paid', '>', 0);

    $termCharges = $invoices->sum(function($q) { return abs($q->amount_paid); });
    $termPayments = $receipts->sum('amount_paid');
    
    $trueBalance = $carriedForward + $termCharges - $termPayments;

    $feeStructures = \App\Models\FeeStructure::where('term_id', $displayTerm->id)
        ->where(function($query) use ($student) {
            $query->where('grade', $student->grade)->whereNull('student_id')
                  ->orWhere('student_id', $student->id);
        })->get();
@endphp

<!-- PRINT HEADER -->
<div class="hidden print:block mb-6">
    <div class="flex justify-between items-center border-b border-black pb-4">
        <div>
            <h2 class="text-xl font-bold text-black">{{ env('SCHOOL_NAME') }}</h2>
            <p class="text-xs text-black">Official Financial Statement</p>
        </div>
        <p class="text-xs text-black">Date: {{ date('d M Y') }}</p>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4 text-xs text-black">
        <div>
            <p><strong>Student:</strong> {{ $student->name }} {{ $student->surname }}</p>
            <p><strong>Student ID:</strong> {{ $student->student_number }}</p>
            <p><strong>Grade:</strong> {{ $student->grade }}</p>
        </div>
        <div class="text-right">
            <p><strong>Term:</strong> {{ $displayTerm->term_name }}</p>
            <p><strong>Academic Year:</strong> {{ $displayTerm->academic_year }}</p>
        </div>
    </div>
</div>

<div class="space-y-4" x-data="{ breakdownModal: false, structureModal: false }">
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden pb-2 border-b border-slate-300 dark:border-slate-800">
        <div>
            <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">Financial Statement</h1>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Statement ledger and payment history</p>
        </div>

        <form action="{{ request()->url() }}" method="GET" id="termSwitcherForm">
            <div class="relative">
                <i class="fa-solid fa-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-slate-900 text-xs pointer-events-none"></i>
                <select name="term_id" 
                        onchange="document.getElementById('termSwitcherForm').submit()"
                        class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg pl-8 pr-8 py-1.5 text-xs font-black text-slate-900 dark:text-slate-100 focus:outline-none">
                    @foreach($sortedTerms as $t)
                        <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }}) {{ $t->is_current ? ' - ACTIVE' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 print:hidden">
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs">
            <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Brought Forward</p>
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 mt-1">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($carriedForward, 2) }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs">
            <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Term Fees Charged</p>
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 mt-1">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termCharges, 2) }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs">
            <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Payments & Credits</p>
            <h3 class="text-sm font-black text-emerald-700 dark:text-emerald-400 mt-1">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termPayments, 2) }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs">
            <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Total Due Balance</p>
            <h3 class="text-sm font-black {{ $trueBalance > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }} mt-1">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($trueBalance, 2) }}</h3>
        </div>
    </div>

    <!-- LEDGER TABLE -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-100 dark:bg-slate-950">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-list-ul text-slate-900"></i>
                <span>Transaction Ledger</span>
            </h3>

            <div class="flex items-center space-x-2 print:hidden">
                <button @click="breakdownModal = true" type="button" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs font-black hover:bg-slate-100 flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Breakdown</span>
                </button>
                <button @click="structureModal = true" type="button" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-xs font-black hover:bg-slate-100 flex items-center space-x-1.5">
                    <i class="fa-solid fa-eye text-xs"></i>
                    <span>Structure</span>
                </button>
                <button onclick="window.print()" type="button" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-black hover:bg-slate-800 flex items-center space-x-1.5">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-900 dark:text-slate-200">
                <thead class="bg-slate-100 dark:bg-slate-950 text-[10px] font-black uppercase text-slate-900 dark:text-slate-300 border-b border-slate-300 dark:border-slate-800">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Description</th>
                        <th class="py-2.5 px-4">Ref Number</th>
                        <th class="py-2.5 px-4 text-right">Debit (Dr)</th>
                        <th class="py-2.5 px-4 text-right">Credit (Cr)</th>
                        <th class="py-2.5 px-4 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Brought Forward -->
                    <tr class="bg-slate-100/60 dark:bg-slate-950/40 font-bold">
                        <td class="py-2.5 px-4 text-slate-700">-</td>
                        <td class="py-2.5 px-4 text-slate-900 dark:text-slate-100">Balance Brought Forward</td>
                        <td class="py-2.5 px-4 text-slate-700">-</td>
                        <td class="py-2.5 px-4 text-right text-slate-700">-</td>
                        <td class="py-2.5 px-4 text-right text-slate-700">-</td>
                        <td class="py-2.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($carriedForward, 2) }}</td>
                    </tr>

                    @php $runningBalance = $carriedForward; @endphp

                    @forelse($termTransactions as $txn)
                        @php
                            $isCharge = $txn->amount_paid < 0;
                            $absAmount = abs($txn->amount_paid);
                            
                            if ($isCharge) {
                                $runningBalance += $absAmount;
                            } else {
                                $runningBalance -= $absAmount;
                            }
                        @endphp
                        <tr class="hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="py-2.5 px-4 font-bold whitespace-nowrap text-slate-900 dark:text-slate-200">{{ \Carbon\Carbon::parse($txn->payment_date)->format('d M Y') }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                                {{ $isCharge ? ($txn->remarks ?? $txn->payment_method) : ('Payment (' . $txn->payment_method . ')') }}
                                @if(!$isCharge && $txn->remarks)
                                    <span class="block text-[10px] font-bold text-slate-700 dark:text-slate-400 mt-0.5">{{ $txn->remarks }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">{{ $txn->reference_no ?? '-' }}</span></td>
                            <td class="py-2.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                                {!! $isCharge ? env('CURRENCY_SYMBOL', '$') . number_format($absAmount, 2) : '<span class="text-slate-400">-</span>' !!}
                            </td>
                            <td class="py-2.5 px-4 text-right font-black text-emerald-700 dark:text-emerald-400">
                                {!! !$isCharge ? env('CURRENCY_SYMBOL', '$') . number_format($absAmount, 2) : '<span class="text-slate-400">-</span>' !!}
                            </td>
                            <td class="py-2.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                                {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($runningBalance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-900 dark:text-slate-400 font-bold">No transactions recorded for this term.</td>
                        </tr>
                    @endforelse

                    <!-- Closing Balance -->
                    <tr class="bg-slate-200 dark:bg-slate-950 font-black border-t-2 border-slate-400 dark:border-slate-700">
                        <td colspan="5" class="py-3 px-4 text-right text-xs uppercase text-slate-900 dark:text-slate-300">Closing Balance:</td>
                        <td class="py-3 px-4 text-right text-sm {{ $runningBalance > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                            {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($runningBalance, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: BREAKDOWN -->
    <div x-show="breakdownModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
        <div @click.outside="breakdownModal = false" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 flex justify-between items-center bg-slate-100 dark:bg-slate-950">
                <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-slate-900"></i>
                    <span>Term Financial Breakdown</span>
                </h3>
                <button @click="breakdownModal = false" class="text-slate-900 hover:text-black dark:text-slate-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
            <div class="p-4 space-y-3 text-xs">
                <div>
                    <h4 class="font-black text-rose-700 uppercase text-[10px] mb-1.5">Charges & Invoices</h4>
                    <div class="space-y-1">
                        @forelse($invoices as $inv)
                            <div class="flex justify-between text-slate-900 dark:text-slate-200 font-bold">
                                <span>{{ $inv->remarks ?? 'General Fee' }}</span>
                                <span class="font-black">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format(abs($inv->amount_paid), 2) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-700 font-bold italic">No charges recorded.</p>
                        @endforelse
                    </div>
                </div>
                <div class="border-t border-slate-300 dark:border-slate-800 pt-2">
                    <h4 class="font-black text-emerald-700 uppercase text-[10px] mb-1.5">Payments & Deductions</h4>
                    <div class="space-y-1">
                        @forelse($receipts as $rec)
                            <div class="flex justify-between text-slate-900 dark:text-slate-200 font-bold">
                                <span>{{ $rec->payment_method }}</span>
                                <span class="font-black text-emerald-700">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($rec->amount_paid, 2) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-700 font-bold italic">No payments recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="p-3 bg-slate-100 dark:bg-slate-950 border-t border-slate-300 dark:border-slate-800 text-right">
                <button @click="breakdownModal = false" class="px-3 py-1.5 rounded bg-slate-900 text-white text-xs font-black">Close</button>
            </div>
        </div>
    </div>

    <!-- MODAL: STRUCTURE -->
    <div x-show="structureModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
        <div @click.outside="structureModal = false" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 flex justify-between items-center bg-slate-100 dark:bg-slate-950">
                <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-1.5">
                    <i class="fa-solid fa-eye text-slate-900"></i>
                    <span>Fee Structure Overview</span>
                </h3>
                <button @click="structureModal = false" class="text-slate-900 dark:text-slate-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
            <div class="p-4 space-y-2 text-xs">
                @php $totalStructure = 0; @endphp
                @forelse($feeStructures as $fee)
                    @php $totalStructure += $fee->amount; @endphp
                    <div class="flex justify-between text-slate-900 dark:text-slate-100 font-bold border-b border-slate-200 dark:border-slate-800 pb-1.5">
                        <span>{{ $fee->fee_name }}</span>
                        <span class="font-black">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($fee->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-slate-700 font-bold italic text-center py-2">No fee structure defined for this grade.</p>
                @endforelse
                <div class="flex justify-between text-xs font-black text-slate-900 dark:text-slate-100 pt-2">
                    <span>Total Expected Term Fees</span>
                    <span>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($totalStructure, 2) }}</span>
                </div>
            </div>
            <div class="p-3 bg-slate-100 dark:bg-slate-950 border-t border-slate-300 dark:border-slate-800 text-right">
                <button @click="structureModal = false" class="px-3 py-1.5 rounded bg-slate-900 text-white text-xs font-black">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection