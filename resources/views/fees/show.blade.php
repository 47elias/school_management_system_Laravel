{{-- resources/views/fees/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Financial Statement | ' . $student->name . ' ' . $student->surname)

@section('header')
    @php
        $displayTerm = $term;
        $totalExpected = (float) $student->expected_total;
        $carriedForward = (float) ($student->carried_forward ?? 0);
        $currentTermFees = (float) ($termRawExpected ?? 0);
        $totalPaid = (float) ($termPaid ?? 0);
        $balance = (float) ($trueBalance ?? 0);
        $hasCredit = $balance < 0;
        $creditAmount = abs($balance);
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full no-print">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Statement: {{ $displayTerm->term_name }} {{ $displayTerm->academic_year }}</h1>
                @if($displayTerm->is_current)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">CURRENT TERM</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Account Ledger for {{ $student->name }} {{ $student->surname }} ({{ $student->student_number }})</p>
        </div>

        <div class="mt-4 sm:mt-0 flex items-center space-x-2.5">
            <form action="{{ request()->url() }}" method="GET" class="inline-block">
                <select name="term_id" onchange="this.form.submit()" 
                        class="px-3 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none">
                    @foreach($allTerms as $t)
                        <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                            View: {{ $t->term_name }} {{ $t->academic_year }}
                        </option>
                    @endforeach
                </select>
            </form>

            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-print mr-1.5"></i> Print
            </button>

            <a href="{{ Route::has('fees.report') ? route('fees.report') : url('/fees/report') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Exit
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ deductModalOpen: false }" class="space-y-6">

        {{-- Printable Header --}}
        <div class="hidden print:block border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black uppercase text-slate-900">{{ env('SCHOOL_NAME', 'Mukahlera College') }}</h2>
                    <p class="text-xs font-bold text-slate-700">STATEMENT OF ACCOUNT - {{ strtoupper($displayTerm->term_name) }} ({{ $displayTerm->academic_year }})</p>
                </div>
                <div class="text-right text-xs">
                    <p><strong>Date:</strong> {{ date('d M Y') }}</p>
                    <p><strong>ID:</strong> {{ $student->student_number }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between no-print">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Column: Summary Card --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 space-y-6 transition-colors">
                    
                    <div class="text-center">
                        <div class="w-20 h-20 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl mx-auto shadow-md mb-3">
                            {{ strtoupper(substr($student->name, 0, 1) . substr($student->surname, 0, 1)) }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $student->name }} {{ $student->surname }}</h3>
                        <p class="text-xs text-slate-400 font-medium">{{ $student->student_number }} | Grade {{ $student->grade }}</p>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Arrears B/F</span>
                            <span class="font-bold {{ $carriedForward > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">${{ number_format($carriedForward, 2) }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Term Fees (Charged)</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">${{ number_format($currentTermFees, 2) }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between bg-slate-50 dark:bg-slate-900/50 px-3 rounded-lg font-bold">
                            <span class="text-slate-800 dark:text-slate-200">Total Invoice</span>
                            <span class="text-indigo-600 dark:text-indigo-400">${{ number_format($totalExpected, 2) }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Total Paid (This Term)</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($totalPaid, 2) }}</span>
                        </div>

                        @if($hasCredit)
                        <div class="py-3 px-3 mt-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 space-y-2">
                            <div class="flex justify-between items-center font-bold">
                                <span><i class="fa-solid fa-star text-amber-500 mr-1"></i> Advance / Credit</span>
                                <span class="text-sm">${{ number_format($creditAmount, 2) }}</span>
                            </div>
                            <button type="button" @click="deductModalOpen = true" class="w-full py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold no-print">
                                Withdraw / Deduct Credit
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl text-center border {{ $balance > 0 ? 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' : 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40' }}">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Outstanding Balance</span>
                        <h2 class="text-3xl font-black mt-1 {{ $balance <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ${{ number_format($balance, 2) }}
                        </h2>
                        @if($balance <= 0)
                            <span class="inline-flex items-center px-2 py-0.5 mt-2 rounded text-[10px] font-bold bg-emerald-500 text-white">ACCOUNT CLEARED</span>
                        @endif
                    </div>

                    <a href="{{ Route::has('fees.create') ? route('fees.create', ['student_id' => $student->id]) : url('/fees/create?student_id=' . $student->id) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold no-print transition-all shadow-sm">
                        <i class="fa-solid fa-plus mr-2"></i> Record Payment
                    </a>
                </div>
            </div>

            {{-- Right Column: Statement of Account / Ledger --}}
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-file-invoice-dollar text-indigo-500 mr-2.5"></i> Statement of Account
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Charge (Dr)</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payment (Cr)</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @php $runningBalance = 0; @endphp

                                @if($carriedForward != 0)
                                    @php $runningBalance += $carriedForward; @endphp
                                    <tr>
                                        <td class="px-6 py-4 text-slate-400">-</td>
                                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-100">
                                            Balance Brought Forward {{ $carriedForward < 0 ? '(Credit)' : '(Arrears)' }}
                                        </td>
                                        @if($carriedForward > 0)
                                            <td class="px-6 py-4 text-right text-rose-600 font-bold">${{ number_format($carriedForward, 2) }}</td>
                                            <td class="px-6 py-4 text-right text-slate-400">-</td>
                                        @else
                                            <td class="px-6 py-4 text-right text-slate-400">-</td>
                                            <td class="px-6 py-4 text-right text-emerald-600 font-bold">${{ number_format(abs($carriedForward), 2) }}</td>
                                        @endif
                                        <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-slate-100">${{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                @endif

                                @if($currentTermFees > 0)
                                    @php $runningBalance += $currentTermFees; @endphp
                                    <tr>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $displayTerm->created_at ? \Carbon\Carbon::parse($displayTerm->created_at)->format('d M Y') : 'Start of Term' }}</td>
                                        <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">Term Fees Charged ({{ $displayTerm->term_name }})</td>
                                        <td class="px-6 py-4 text-right text-rose-600 font-bold">${{ number_format($currentTermFees, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-slate-400">-</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-slate-100">${{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                @endif

                                @forelse($student->payments->where('term_id', $displayTerm->id)->where('payment_method', '!=', 'Term Invoice')->sortBy('payment_date') as $payment)
                                    @php $runningBalance -= $payment->amount_paid; @endphp
                                    <tr>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                                {{ $payment->payment_method === 'Credit Withdrawal' ? 'Credit Withdrawal' : ($payment->amount_paid < 0 ? 'Adjustment / Reversal' : 'Payment') }} ({{ $payment->payment_method }})
                                            </span>
                                            @if($payment->reference_no || $payment->remarks)
                                                <span class="block text-[11px] text-slate-400">
                                                    {{ $payment->reference_no ? 'Ref: ' . $payment->reference_no : '' }} {{ $payment->remarks ? '- ' . $payment->remarks : '' }}
                                                </span>
                                            @endif
                                        </td>
                                        @if($payment->amount_paid < 0)
                                            <td class="px-6 py-4 text-right text-rose-600 font-bold">${{ number_format(abs($payment->amount_paid), 2) }}</td>
                                            <td class="px-6 py-4 text-right text-slate-400">-</td>
                                        @else
                                            <td class="px-6 py-4 text-right text-slate-400">-</td>
                                            <td class="px-6 py-4 text-right text-emerald-600 font-bold">${{ number_format($payment->amount_paid, 2) }}</td>
                                        @endif
                                        <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-slate-100">${{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                @empty
                                    @if($currentTermFees == 0 && $carriedForward == 0)
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            No financial activity recorded for this term.
                                        </td>
                                    </tr>
                                    @endif
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-slate-800 dark:text-slate-100">CLOSING BALANCE:</td>
                                    <td class="px-6 py-4 text-right font-black text-sm {{ $runningBalance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        ${{ number_format($runningBalance, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Printable Signatures --}}
                <div class="hidden print:block mt-16 pt-8 border-t border-slate-300">
                    <div class="grid grid-cols-2 gap-12 text-center text-xs font-bold text-slate-800">
                        <div class="border-t border-slate-800 pt-2">Bursar / Accounts</div>
                        <div class="border-t border-slate-800 pt-2">Parent / Guardian</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deduct Credit Modal --}}
        @if($hasCredit)
        <div x-show="deductModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div @click="deductModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-sm p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-xl z-10 border border-slate-200 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 mr-2"></i> Deduct / Withdraw Credit
                    </h3>

                    <form action="{{ Route::has('fees.deduct_credit') ? route('fees.deduct_credit', $student->id) : url('/fees/deduct-credit/' . $student->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $displayTerm->id }}">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Amount to Withdraw ($)</label>
                            <input type="number" step="0.01" name="amount" max="{{ $creditAmount }}" required placeholder="0.00"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">Available Credit: ${{ number_format($creditAmount, 2) }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Reason / Remarks</label>
                            <textarea name="remarks" rows="2" placeholder="e.g. Refunded to parent"
                                      class="w-full px-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2 pt-2">
                            <button type="button" @click="deductModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold">
                                Confirm Deduction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>
@endsection