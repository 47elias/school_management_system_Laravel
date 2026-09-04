@extends('layouts.student')

@section('content')
{{-- 1. DATA PREPARATION --}}
@php
    // Ensure Active Term is always first in the list
    $sortedTerms = $allTerms->sortByDesc('is_current')->values();

    $requestedTermId = request('term_id');
    if ($requestedTermId) {
        $displayTerm = $sortedTerms->firstWhere('id', $requestedTermId);
    } else {
        $displayTerm = $sortedTerms->first();
    }

    // Calculate Balance Brought Forward (Prior Terms)
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

    // Get Current Term Transactions
    $termTransactions = \App\Models\Payment::where('student_id', $student->id)
        ->where('term_id', $displayTerm->id)
        ->orderBy('payment_date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    // Separate Charges and Deductions/Payments for Breakdown
    $invoices = $termTransactions->where('amount_paid', '<', 0);
    $receipts = $termTransactions->where('amount_paid', '>', 0);

    $termCharges = $invoices->sum(function($q) { return abs($q->amount_paid); });
    $termPayments = $receipts->sum('amount_paid');
    
    $trueBalance = $carriedForward + $termCharges - $termPayments;

    // Fetch Fee Structure for the Modal Popup
    $feeStructures = \App\Models\FeeStructure::where('term_id', $displayTerm->id)
        ->where(function($query) use ($student) {
            $query->where('grade', $student->grade)->whereNull('student_id')
                  ->orWhere('student_id', $student->id);
        })->get();
@endphp

{{-- PRINT-ONLY HEADER --}}
<div class="visible-print-block" style="margin-bottom: 30px;">
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header" style="border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; font-size: 24px;">
                <i class="fa fa-university text-gray-800"></i> {{ env('SCHOOL_NAME') }}
                <small class="pull-right text-sm text-gray-500">Date: {{ date('d M Y') }}</small>
            </h2>
        </div>
    </div>
    <div class="row invoice-info mt-4" style="font-size: 16px;">
        <div class="col-xs-6 invoice-col">
            <address>
                <strong>Student:</strong> {{ $student->name }} {{ $student->surname }}<br>
                <strong>ID Number:</strong> {{ $student->student_number }}<br>
                <strong>Grade:</strong> {{ $student->grade }}
            </address>
        </div>
        <div class="col-xs-6 invoice-col text-right">
            <address>
                <strong>Statement Term:</strong> {{ $displayTerm->term_name }}<br>
                <strong>Academic Year:</strong> {{ $displayTerm->academic_year }}
            </address>
        </div>
    </div>
</div>

{{-- SCREEN HEADER --}}
<section class="content-header no-print flex-header">
    <div>
        <h1 class="modern-title">Financial Statement</h1>
        <p class="modern-subtitle">Track your invoices, payments, and account balance</p>
    </div>
    <div class="term-switcher">
        <form action="{{ request()->url() }}" method="GET" id="termSwitcherForm">
            <div class="select-wrapper">
                <i class="fa fa-calendar-alt select-icon"></i>
                <select name="term_id" class="modern-select" onchange="document.getElementById('termSwitcherForm').submit()">
                    @foreach($sortedTerms as $t)
                        <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }}) {{ $t->is_current ? ' - ACTIVE' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</section>

<section class="content">
    {{-- Modern Summary Cards --}}
    <div class="row no-print">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="stat-card bg-slate">
                <div class="stat-icon"><i class="fa fa-history"></i></div>
                <div class="stat-details">
                    <p>Brought Forward</p>
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($carriedForward, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="stat-card bg-blue">
                <div class="stat-icon"><i class="fa fa-file-invoice"></i></div>
                <div class="stat-details">
                    <p>Term Fees Charged</p>
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termCharges, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="stat-card bg-green">
                <div class="stat-icon"><i class="fa fa-wallet"></i></div>
                <div class="stat-details">
                    <p>Payments & Deductions</p>
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termPayments, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="stat-card {{ $trueBalance > 0 ? 'bg-red' : 'bg-emerald' }}">
                <div class="stat-icon"><i class="fa fa-balance-scale"></i></div>
                <div class="stat-details">
                    <p>Total Due Balance</p>
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($trueBalance, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- FULL WIDTH COLUMN: LEDGER HISTORY --}}
        <div class="col-md-12">
            <div class="modern-card">
                <div class="card-header flex-header-actions">
                    <h3 class="card-title"><i class="fa fa-list-ul text-gray-500 mr-2"></i> Transaction Ledger</h3>
                    
                    <div class="action-buttons no-print">
                        <button type="button" class="btn-secondary-action" data-toggle="modal" data-target="#termBreakdownModal">
                            <i class="fa fa-pie-chart"></i> Term Breakdown
                        </button>
                        <button type="button" class="btn-secondary-action" data-toggle="modal" data-target="#feeStructureModal">
                            <i class="fa fa-eye"></i> Fee Structure
                        </button>
                        <button onclick="window.print()" class="btn-print"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Ref</th>
                                    <th class="text-right">Charge (Dr)</th>
                                    <th class="text-right">Payment (Cr)</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Brought Forward Row --}}
                                <tr class="bg-light-row text-base">
                                    <td class="text-gray-500">-</td>
                                    <td class="font-semibold text-gray-800">Balance Brought Forward</td>
                                    <td class="text-gray-500">-</td>
                                    <td class="text-right text-gray-400">-</td>
                                    <td class="text-right text-gray-400">-</td>
                                    <td class="text-right font-bold text-gray-900">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($carriedForward, 2) }}</td>
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
                                    <tr class="hover-row text-base">
                                        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($txn->payment_date)->format('d M Y') }}</td>
                                        <td>
                                            @if($isCharge)
                                                <div class="flex-align">
                                                    <span class="font-medium text-gray-800">{{ $txn->remarks ?? $txn->payment_method }}</span>
                                                </div>
                                            @else
                                                <div class="flex-align">
                                                    <div>
                                                        <span class="font-medium text-gray-800">Payment ({{ $txn->payment_method }})</span>
                                                        @if($txn->remarks) <span class="block text-sm text-gray-500 mt-1">{{ $txn->remarks }}</span> @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td><span class="badge-ref">{{ $txn->reference_no ?? '-' }}</span></td>
                                        <td class="text-right amount-cell">
                                            @if($isCharge)
                                                <span class="text-gray-900 font-medium">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($absAmount, 2) }}</span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right amount-cell">
                                            @if(!$isCharge)
                                                <span class="text-emerald-600 font-medium">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($absAmount, 2) }}</span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-bold text-gray-900">
                                            {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($runningBalance, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10 text-gray-500 text-lg">
                                            <i class="fa fa-folder-open text-4xl mb-3 text-gray-300 block"></i>
                                            No transactions recorded for this term.
                                        </td>
                                    </tr>
                                @endforelse
                                
                                {{-- Final Term Balance Row --}}
                                <tr class="footer-row">
                                    <td colspan="5" class="text-right text-base tracking-wide uppercase text-gray-600 font-semibold">Closing Balance:</td>
                                    <td class="text-right font-bold text-xl {{ $runningBalance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($runningBalance, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- MODAL: TERM BREAKDOWN --}}
<div class="modal fade" id="termBreakdownModal" tabindex="-1" role="dialog" aria-labelledby="termBreakdownModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; margin-top: -5px;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title font-bold text-gray-800" id="termBreakdownModalLabel">
                    <i class="fa fa-pie-chart text-blue-500 mr-2"></i> Term Breakdown
                </h4>
                <p class="text-sm text-gray-500 mt-1 mb-0">{{ $displayTerm->term_name }} ({{ $displayTerm->academic_year }})</p>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="breakdown-section">
                    <h4 class="breakdown-title text-red-600"><i class="fa fa-arrow-up"></i> Fees Charged & Billed</h4>
                    <ul class="breakdown-list">
                        @forelse($invoices as $inv)
                            <li>
                                <span class="breakdown-label">{{ $inv->remarks ?? 'General Fee' }}</span>
                                <span class="breakdown-amount font-medium">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format(abs($inv->amount_paid), 2) }}</span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-base italic">No fees charged this term.</li>
                        @endforelse
                        <li class="breakdown-subtotal text-lg mt-2">
                            <span>Total Charges</span>
                            <span class="text-red-600">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termCharges, 2) }}</span>
                        </li>
                    </ul>
                </div>
                
                <div class="breakdown-section border-t bg-gray-50">
                    <h4 class="breakdown-title text-emerald-600"><i class="fa fa-arrow-down"></i> Payments & Deductions</h4>
                    <ul class="breakdown-list">
                        @forelse($receipts as $rec)
                            <li>
                                <span class="breakdown-label">{{ $rec->payment_method }} {{ $rec->remarks ? '('.$rec->remarks.')' : '' }}</span>
                                <span class="breakdown-amount font-medium">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($rec->amount_paid, 2) }}</span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-base italic">No payments or deductions.</li>
                        @endforelse
                        <li class="breakdown-subtotal text-lg mt-2">
                            <span>Total Deductions</span>
                            <span class="text-emerald-600">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termPayments, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 15px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: FEE STRUCTURE --}}
<div class="modal fade" id="feeStructureModal" tabindex="-1" role="dialog" aria-labelledby="feeStructureModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; margin-top: -5px;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title font-bold text-gray-800" id="feeStructureModalLabel">
                    <i class="fa fa-info-circle text-blue-500 mr-2"></i> Official Fee Structure
                </h4>
                <p class="text-sm text-gray-500 mt-1 mb-0">Grade {{ $student->grade }} | {{ $displayTerm->term_name }} ({{ $displayTerm->academic_year }})</p>
            </div>
            <div class="modal-body" style="padding: 0;">
                <table class="modern-table" style="margin: 0;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th style="padding: 15px 20px;">Fee Description</th>
                            <th class="text-right" style="padding: 15px 20px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalStructure = 0; @endphp
                        @forelse($feeStructures as $fee)
                            @php $totalStructure += $fee->amount; @endphp
                            <tr class="text-base border-b">
                                <td style="padding: 15px 20px;" class="text-gray-800">
                                    {{ $fee->fee_name }}
                                    @if($fee->student_id)
                                        <span class="label label-warning" style="margin-left: 8px;">Specific to you</span>
                                    @endif
                                </td>
                                <td class="text-right font-medium text-gray-900" style="padding: 15px 20px;">
                                    {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($fee->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-gray-500 py-6 text-base italic">No fee structure defined for your grade this term.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f1f5f9;">
                            <td class="text-right font-bold text-gray-700 uppercase" style="padding: 15px 20px;">Total Expected Fees:</td>
                            <td class="text-right font-bold text-lg text-gray-900" style="padding: 15px 20px;">
                                {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($totalStructure, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 15px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODERN UI STYLES --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc; }

    /* Header & Filters */
    .flex-header { display: flex; justify-content: space-between; align-items: center; padding: 25px 15px; }
    .modern-title { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.5px; }
    .modern-subtitle { font-size: 15px; color: #64748b; margin: 6px 0 0 0; }
    
    .select-wrapper { position: relative; display: inline-block; }
    .select-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none; font-size: 15px; }
    .modern-select { 
        appearance: none; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; 
        padding: 12px 40px 12px 40px; font-size: 15px; font-weight: 600; color: #1e293b;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); cursor: pointer; min-width: 220px; transition: all 0.2s;
    }
    .modern-select:hover { border-color: #94a3b8; }
    .modern-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }

    /* Stat Cards */
    .stat-card { 
        display: flex; align-items: center; padding: 24px; border-radius: 12px; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); 
        margin-bottom: 24px; color: #fff; position: relative; overflow: hidden;
    }
    .stat-card.bg-slate { background: linear-gradient(135deg, #475569, #334155); }
    .stat-card.bg-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    .stat-card.bg-green { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-card.bg-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .stat-card.bg-emerald { background: linear-gradient(135deg, #059669, #047857); }
    
    .stat-icon { font-size: 38px; opacity: 0.9; margin-right: 20px; }
    .stat-details p { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; font-weight: 600; }
    .stat-details h3 { margin: 6px 0 0 0; font-size: 28px; font-weight: 700; }

    /* Modern Card */
    .modern-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: #fff; }
    .flex-header-actions { display: flex; justify-content: space-between; align-items: center; }
    .card-title { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
    .p-0 { padding: 0 !important; }

    /* Action Buttons in Header */
    .action-buttons { display: flex; gap: 10px; }
    .btn-secondary-action {
        background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 8px 16px; border-radius: 6px;
        font-size: 14px; font-weight: 600; transition: all 0.2s; cursor: pointer;
    }
    .btn-secondary-action:hover { background: #dbeafe; color: #1d4ed8; }

    .btn-print { 
        background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 6px; 
        font-size: 14px; font-weight: 600; transition: all 0.2s; cursor: pointer;
    }
    .btn-print:hover { background: #e2e8f0; color: #0f172a; }
    
    /* Breakdown Section inside Modal */
    .breakdown-section { padding: 20px 24px; }
    .bg-gray-50 { background-color: #f8fafc; }
    .breakdown-title { font-size: 15px; text-transform: uppercase; font-weight: 700; margin: 0 0 16px 0; letter-spacing: 0.5px; }
    .breakdown-list { list-style: none; padding: 0; margin: 0; }
    .breakdown-list li { display: flex; justify-content: space-between; font-size: 15px; padding: 8px 0; color: #475569; }
    .breakdown-label { font-weight: 500; }
    .breakdown-amount { font-family: ui-monospace, monospace; }
    .breakdown-subtotal { border-top: 1px dashed #cbd5e1; margin-top: 12px; padding-top: 12px !important; font-weight: 700; color: #0f172a !important; }
    .border-t { border-top: 1px solid #e2e8f0; }

    /* Tables */
    .modern-table { width: 100%; border-collapse: collapse; }
    .modern-table th { 
        background: #f8fafc; padding: 16px 20px; font-size: 13px; font-weight: 700; 
        color: #475569; text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }
    .modern-table th.text-right { text-align: right; }
    
    .modern-table td { padding: 16px 20px; font-size: 15px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .hover-row:hover { background-color: #f8fafc; }
    .bg-light-row { background-color: #f8fafc; }
    .footer-row { background-color: #f8fafc; border-top: 2px solid #cbd5e1; }
    
    /* Utilities */
    .flex-align { display: flex; align-items: center; gap: 12px; }
    .badge-ref { background: #f1f5f9; padding: 6px 10px; border-radius: 6px; font-family: ui-monospace, monospace; font-size: 13px; color: #475569; border: 1px solid #e2e8f0; }
    
    .text-base { font-size: 15px !important; }
    .text-lg { font-size: 18px !important; }
    .text-xl { font-size: 20px !important; }
    .font-medium { font-weight: 500; }
    .font-semibold { font-weight: 600; }
    .font-bold { font-weight: 700; }
    
    .text-gray-300 { color: #cbd5e1; }
    .text-gray-400 { color: #94a3b8; }
    .text-gray-500 { color: #64748b; }
    .text-gray-600 { color: #475569; }
    .text-gray-700 { color: #334155; }
    .text-gray-800 { color: #1e293b; }
    .text-gray-900 { color: #0f172a; }
    
    .text-red-600 { color: #dc2626; }
    .text-emerald-600 { color: #059669; }
    .text-blue-500 { color: #3b82f6; }
    
    .whitespace-nowrap { white-space: nowrap; }
    .block { display: block; }
    .mt-1 { margin-top: 4px; }
    .mt-2 { margin-top: 8px; }
    .mr-2 { margin-right: 8px; }
    .py-10 { padding-top: 40px; padding-bottom: 40px; }

    @media print {
        body { background: #fff !important; }
        .no-print { display: none !important; }
        .visible-print-block { display: block !important; }
        .modern-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .modern-table th { background: transparent !important; border-bottom: 2px solid #000; color: #000; }
        .footer-row { background: transparent !important; border-top: 2px solid #000; }
        .bg-light-row { background: transparent !important; }
        .badge-ref { border: 1px solid #ccc; background: transparent !important; }
    }
</style>
@endsection