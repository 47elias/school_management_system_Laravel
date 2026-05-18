<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Financial Profile | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    {{-- AdminLTE CSS & Dependencies --}}
    @include('components.adminlte')

    <style>
        /* Profile Styling */
        .bg-initials-circle {
            width: 100px; height: 100px; line-height: 100px;
            margin: 0 auto 15px; font-size: 35px; font-weight: bold;
            color: #fff; background-color: #3c8dbc;
            border-radius: 50%; text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .balance-box {
            margin: 15px 0; padding: 20px;
            background: #fff5f5; border: 1px solid #ebccd1; border-radius: 4px;
        }
        .text-black { font-weight: 900; color: #000; }
        .text-bold { font-weight: bold; }

        /* Term Switcher Styling */
        .term-selector-container {
            display: inline-block; margin-left: 10px; vertical-align: middle;
        }
        .term-select-input {
            height: 30px; padding: 2px 10px; font-size: 13px;
            border-radius: 4px; border: 1px solid #ccc; background: #fff;
            cursor: pointer;
        }

        /* Print Utilities */
        @media print {
            .no-print, .main-header, .main-sidebar, .main-footer, .btn, .term-selector-container { display: none !important; }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: #fff !important; }
            body { background: #fff !important; }
            .box { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 10px; }
            .col-md-4 { width: 35% !important; float: left !important; }
            .col-md-8 { width: 65% !important; float: left !important; }
            .print-header { display: block !important; }
            .box-primary, .box-info { border-top: 3px solid #333 !important; }
        }
        .print-header { display: none; margin-bottom: 30px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">

            {{-- 1. DATA PREPARATION --}}
            @php
                $displayTerm = $term; // Passed from Controller

                // Use the calculated totals from the controller
                $totalExpected = (float) $student->expected_total;
                $carriedForward = (float) ($student->carried_forward ?? 0);
                $currentTermFees = (float) ($termRawExpected ?? 0);

                $totalPaid = (float) ($termPaid ?? 0);
                $balance = (float) ($trueBalance ?? 0);

                // Billing Setup
                $billingDates = $displayTerm->getBillingMonthDates() ?? [];

                // Determine monthly target based on term structure / duration
                $monthCount = count($billingDates) > 0 ? count($billingDates) : 1;
                $monthlyTarget = $totalExpected / $monthCount;

                // Credit/Advance Logic (Negative balance means they have credit)
                $hasCredit = $balance < 0;
                $creditAmount = abs($balance);
            @endphp

            {{-- Print-Only Header --}}
            <div class="print-header">
                <table style="width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px;">
                    <tr>
                        <td>
                            <h2 style="margin:0; font-weight:bold;">{{ env('SCHOOL_NAME') }}</h2>
                            <p style="margin:0; font-size: 14px;">STUDENT ACCOUNT STATEMENT - {{ strtoupper($displayTerm->term_name) }} ({{ $displayTerm->academic_year }})</p>
                        </td>
                        <td style="text-align: right;">
                            <p style="margin:0;"><strong>Date:</strong> {{ date('d M Y') }}</p>
                            <p style="margin:0;"><strong>ID:</strong> {{ $student->student_number }}</p>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Screen Header --}}
            <section class="content-header no-print">
                <h1>
                    Statement: <span class="text-primary">{{ $displayTerm->term_name }} {{ $displayTerm->academic_year }}</span>
                    @if($displayTerm->is_current)
                        <small class="label label-success" style="font-size: 11px; vertical-align: middle;">CURRENT TERM</small>
                    @endif

                    <div class="term-selector-container">
                        <form action="{{ request()->url() }}" method="GET" id="termSwitcherForm">
                            <select name="term_id" class="term-select-input" onchange="document.getElementById('termSwitcherForm').submit()">
                                @foreach($allTerms as $t)
                                    <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                                        View: {{ $t->term_name }} {{ $t->academic_year }} {{ $t->is_current ? '(Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="pull-right">
                        <button onclick="window.print()" class="btn btn-default btn-flat btn-sm">
                            <i class="fa fa-print"></i> PRINT
                        </button>
                        <a href="{{ route('fees.report') }}" class="btn btn-default btn-flat btn-sm">
                            <i class="fa fa-arrow-left"></i> EXIT
                        </a>
                    </div>
                </h1>
            </section>

            <section class="content">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible no-print">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="row">

                    {{-- Left Column: Summary Card --}}
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-body box-profile">
                                <div class="no-print">
                                    <div class="bg-initials-circle">
                                        {{ strtoupper(substr($student->name, 0, 1) . substr($student->surname, 0, 1)) }}
                                    </div>
                                </div>

                                <h3 class="profile-username text-center text-bold">{{ $student->name }} {{ $student->surname }}</h3>
                                <p class="text-muted text-center">{{ $student->student_number }} | {{ $student->grade }}</p>

                                <ul class="list-group list-group-unbordered" style="margin-top: 20px;">
                                    <li class="list-group-item">
                                        <b>Arrears B/F</b> <span class="pull-right text-bold {{ $carriedForward > 0 ? 'text-red' : 'text-green' }}">${{ number_format($carriedForward, 2) }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Current Term Fees</b> <span class="pull-right text-bold">${{ number_format($currentTermFees, 2) }}</span>
                                    </li>
                                    <li class="list-group-item" style="background: #f9f9f9;">
                                        <b>Total Invoice</b> <span class="pull-right text-bold">${{ number_format($totalExpected, 2) }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Total Paid (This Term)</b> <span class="pull-right text-bold text-green">${{ number_format($totalPaid, 2) }}</span>
                                    </li>

                                    @if($hasCredit)
                                    <li class="list-group-item" style="background: #e7faf0; border: 1px solid #c3e6cb;">
                                        <b class="text-green"><i class="fa fa-star"></i> Advance / Credit</b>
                                        <span class="pull-right text-bold text-green">${{ number_format($creditAmount, 2) }}</span>
                                        <div class="text-center no-print" style="margin-top: 10px;">
                                            {{-- Updated to Deduct Credit Trigger --}}
                                            <button type="button" class="btn btn-xs btn-danger btn-flat" data-toggle="modal" data-target="#deductModal">
                                                <i class="fa fa-minus"></i> WITHDRAW / DEDUCT
                                            </button>
                                        </div>
                                    </li>
                                    @endif
                                </ul>

                                <div class="balance-box text-center">
                                    <small class="text-uppercase text-muted">Total Outstanding Balance</small>
                                    <h2 class="{{ $balance <= 0 ? 'text-green' : 'text-red' }} text-black" style="margin: 5px 0;">
                                        ${{ number_format($balance, 2) }}
                                    </h2>
                                    @if($student->monthly_arrears > 0 && $balance > 0)
                                        <div class="label label-danger">Overdue: ${{ number_format($student->monthly_arrears, 2) }}</div>
                                    @endif
                                    @if($balance <= 0)
                                        <div class="label label-success">ACCOUNT CLEARED</div>
                                    @endif
                                </div>

                                <a href="{{ route('fees.create', ['student_id' => $student->id]) }}"
                                   class="btn btn-primary btn-block btn-flat no-print text-bold">
                                    <i class="fa fa-plus"></i> RECORD PAYMENT
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Details --}}
                    <div class="col-md-8">

                        {{-- Payment Schedule --}}
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold">
                                    <i class="fa fa-calendar-check-o"></i> MONTHLY INSTALLMENTS
                                </h3>
                            </div>
                            <div class="box-body no-padding">
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>Month</th>
                                            <th class="text-center">Target</th>
                                            <th class="text-center">Paid</th>
                                            <th class="text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $pool = $totalPaid;
                                            $now = \Carbon\Carbon::now()->startOfMonth();
                                        @endphp
                                        @foreach($billingDates as $date)
                                            @php
                                                $dateObj = \Carbon\Carbon::parse($date)->startOfMonth();
                                                $paidInThisSlot = min($monthlyTarget, $pool);
                                                $pool = max(0, $pool - $paidInThisSlot);
                                                $shortfall = $monthlyTarget - $paidInThisSlot;
                                                $isCleared = $shortfall < 0.01;
                                                $isOverdue = $now->gte($dateObj) && !$isCleared;
                                            @endphp
                                            <tr>
                                                <td class="text-bold">{{ $dateObj->format('F Y') }}</td>
                                                <td class="text-center">${{ number_format($monthlyTarget, 2) }}</td>
                                                <td class="text-center text-blue">${{ number_format($paidInThisSlot, 2) }}</td>
                                                <td class="text-right">
                                                    @if($isCleared)
                                                        <span class="label label-success">PAID</span>
                                                    @elseif($isOverdue)
                                                        <span class="label label-danger">OVERDUE</span>
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

                        {{-- Transaction History --}}
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold"><i class="fa fa-list"></i> TRANSACTION LOG</h3>
                            </div>
                            <div class="box-body no-padding">
                                <table class="table table-hover">
                                    <tbody>
                                        @forelse($student->payments->where('term_id', $displayTerm->id)->sortByDesc('payment_date') as $payment)
                                        <tr>
                                            <td style="width: 50px; text-align: center;">
                                                <i class="fa {{ $payment->amount_paid < 0 ? 'fa-minus text-red' : 'fa-check text-green' }}"></i>
                                            </td>
                                            <td>
                                                <strong>{{ $payment->payment_method }}</strong><br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="text-muted small">Ref: {{ $payment->reference_no ?? 'N/A' }}</span><br>
                                                <span class="text-muted small">{{ $payment->remarks }}</span>
                                            </td>
                                            <td class="text-right text-bold {{ $payment->amount_paid < 0 ? 'text-red' : 'text-green' }}">
                                                {{ $payment->amount_paid < 0 ? '-' : '+' }} ${{ number_format(abs($payment->amount_paid), 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center" style="padding: 30px;">
                                                No payments recorded for {{ $displayTerm->term_name }}.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Print Signature Block --}}
                        <div class="print-header" style="margin-top: 60px;">
                            <div class="row">
                                <div class="col-xs-5">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; text-align: center;">
                                        <strong>Bursar / Accounts</strong>
                                    </div>
                                </div>
                                <div class="col-xs-2"></div>
                                <div class="col-xs-5">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; text-align: center;">
                                        <strong>Parent / Guardian</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>

        {{-- Deduction Modal (No Print) --}}
        @if($hasCredit)
        <div class="modal fade no-print" id="deductModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <form action="{{ route('fees.deduct_credit', $student->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-red">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <h4 class="modal-title">Deduct Credit</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="term_id" value="{{ $displayTerm->id }}">
                            <div class="form-group">
                                <label>Amount to Withdraw ($)</label>
                                <input type="number" step="0.01" name="amount" max="{{ $creditAmount }}" class="form-control" placeholder="0.00" required>
                                <p class="help-block small">Available Credit: ${{ number_format($creditAmount, 2) }}</p>
                            </div>
                            <div class="form-group">
                                <label>Reason/Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Refunded to parent"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger btn-flat">Confirm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @include('layouts.footer')
    </div>

    @include('components.scripts')
</body>
</html>
