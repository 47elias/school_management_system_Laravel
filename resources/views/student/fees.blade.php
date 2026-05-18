@extends('layouts.student')

@section('content')
{{-- 1. DATA PREPARATION --}}
@php
    $requestedTermId = request('term_id');
    if ($requestedTermId) {
        $displayTerm = $allTerms->firstWhere('id', $requestedTermId);
    } else {
        $displayTerm = $allTerms->firstWhere('is_current', true) ?? $allTerms->first();
    }

    $allTimePaid = \App\Models\Payment::where('student_id', $student->id)->sum('amount_paid');

    // Robust Fee Detection (matching your DB structure)
    $structureFee = \DB::table('fee_structures')
        ->where('grade', $student->grade)
        ->where('term_id', $displayTerm->id)
        ->value('amount');

    $termTotalExpected = (float) ($displayTerm->declared_fee ?? $structureFee ?? $student->expected_total ?? 0);
    $termTotalPaid = (float) $student->payments->where('term_id', $displayTerm->id)->sum('amount_paid');
    $termBalance = $termTotalExpected - $termTotalPaid;

    $billingDates = $displayTerm->getBillingMonthDates() ?? [];
    $monthCount = count($billingDates) > 0 ? count($billingDates) : 3;
    $monthlyTarget = ($termTotalExpected > 0) ? ($termTotalExpected / $monthCount) : 0;
@endphp

{{-- PRINT-ONLY HEADER --}}
<div class="visible-print-block" style="margin-bottom: 30px;">
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa fa-university"></i> {{ env('SCHOOL_NAME') }}
                <small class="pull-right">Date: {{ date('d/m/Y') }}</small>
            </h2>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-xs-6 invoice-col">
            <strong>Student:</strong> {{ $student->name }} {{ $student->surname }} ({{ $student->student_number }})<br>
            <strong>Grade:</strong> {{ $student->grade }}
        </div>
        <div class="col-xs-6 invoice-col text-right">
            <strong>Term:</strong> {{ $displayTerm->term_name }}<br>
            <strong>Academic Year:</strong> {{ $displayTerm->academic_year }}
        </div>
    </div>
</div>

{{-- SCREEN HEADER --}}
<section class="content-header no-print">
    <h1>
        My Financial Statement
        <small>Student View</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <form action="{{ request()->url() }}" method="GET" id="termSwitcherForm">
                <select name="term_id" class="form-control input-sm" onchange="document.getElementById('termSwitcherForm').submit()">
                    @foreach($allTerms as $t)
                        <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                            {{ $t->term_name }} {{ $t->academic_year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </li>
    </ol>
</section>

<section class="content">
    {{-- Summary Cards --}}
    <div class="row no-print">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termTotalExpected, 2) }}</h3>
                    <p>Expected for {{ $displayTerm->term_name }}</p>
                </div>
                <div class="icon"><i class="fa fa-file-text-o"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termTotalPaid, 2) }}</h3>
                    <p>Total Paid this Term</p>
                </div>
                <div class="icon"><i class="fa fa-check-square-o"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="small-box {{ $termBalance > 0 ? 'bg-red' : 'bg-olive' }}">
                <div class="inner">
                    <h3>{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($termBalance, 2) }}</h3>
                    <p>Current Balance</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN: MONTHLY PROGRESS --}}
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calendar"></i> Monthly Analysis</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr class="bg-gray">
                                <th>Month</th>
                                <th class="text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $pool = $termTotalPaid; @endphp
                            @foreach($billingDates as $date)
                                @php
                                    $dateObj = \Carbon\Carbon::parse($date)->startOfMonth();
                                    $paidInThisSlot = min($monthlyTarget, $pool);
                                    $pool = max(0, $pool - $paidInThisSlot);
                                    $isCleared = ($monthlyTarget - $paidInThisSlot) < 0.01;
                                @endphp
                                <tr>
                                    <td>{{ $dateObj->format('F Y') }}</td>
                                    <td class="text-right">
                                        @if($isCleared)
                                            <span class="label label-success">PAID</span>
                                        @else
                                            <span class="label label-warning">PENDING</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: TRANSACTION HISTORY (Term instead of Action) --}}
        <div class="col-md-8">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list-ul"></i> Payment History</h3>
                    <div class="box-tools pull-right">
                        <button onclick="window.print()" class="btn btn-default btn-sm no-print"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Term</th> {{-- Replaced Action with Term --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $allPayments = \App\Models\Payment::where('student_id', $student->id)
                                        ->with('term')
                                        ->orderBy('payment_date', 'desc')
                                        ->get();
                                @endphp
                                @forelse($allPayments as $payment)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td><span class="text-muted">{{ $payment->payment_method }}</span></td>
                                    <td><code>{{ $payment->reference_no ?? 'N/A' }}</code></td>
                                    <td class="text-right text-bold">
                                        {{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($payment->amount_paid, 2) }}
                                    </td>
                                    <td class="text-center small">
                                        {{ $payment->term->term_name ?? '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding: 30px;">No transaction records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .mb-0 { margin-bottom: 0 !important; }
    .small-box h3 { font-size: 28px; }
    @media print {
        .no-print { display: none !important; }
        .visible-print-block { display: block !important; }
        .box { border: 1px solid #eee !important; }
    }
</style>
@endsection
