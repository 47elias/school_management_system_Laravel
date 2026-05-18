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
        @include('layouts.receptionist_sidebar')

        <div class="content-wrapper">

            {{-- 1. DATA PREPARATION (Updated for Carry Forward) --}}
            @php
                $displayTerm = $term; // Passed from Controller

                // Totals already calculated in Controller
                $totalExpected = (float) $student->expected_total;
                $carriedForward = (float) ($student->carried_forward ?? 0);
                $currentTermFees = $totalExpected - $carriedForward;

                $totalPaid = (float) $student->payments->where('term_id', $displayTerm->id)->sum('amount_paid');
                $balance = $student->calculated_balance;

                // Billing Setup
                $billingDates = $displayTerm->getBillingMonthDates() ?? [];
                // Monthly target remains $50 as per your controller logic
                $monthlyTarget = 50.00;
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
                        <a href="{{ route('receptionist.students.index') }}" class="btn btn-default btn-flat btn-sm">
                            <i class="fa fa-arrow-left"></i> EXIT
                        </a>
                    </div>
                </h1>
            </section>

            <section class="content">
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
                                        <b>Arrears B/F</b> <span class="pull-right text-bold text-red">${{ number_format($carriedForward, 2) }}</span>
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
                                </ul>

                                <div class="balance-box text-center">
                                    <small class="text-uppercase text-muted">Total Outstanding Balance</small>
                                    <h2 class="text-red text-black" style="margin: 5px 0;">${{ number_format($balance, 2) }}</h2>
                                    @if($student->monthly_arrears > 0)
                                        <div class="label label-danger">Overdue: ${{ number_format($student->monthly_arrears, 2) }}</div>
                                    @endif
                                </div>

                                <a href="{{ route('receptionist.payments.create', ['student_id' => $student->id]) }}"
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
                                            // Pool uses current term payments.
                                            // In your controller logic, pool covers targets chronologically.
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
                                <div class="pad text-muted">
                                    <small><i class="fa fa-info-circle"></i> Installments are calculated based on the fixed $50.00 monthly logic.</small>
                                </div>
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
                                            <td style="width: 50px; text-align: center;"><i class="fa fa-check text-green"></i></td>
                                            <td>
                                                <strong>Payment Received</strong><br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="text-muted small">Ref: {{ $payment->reference_no ?? 'N/A' }}</span><br>
                                                <span class="label label-default">{{ $payment->payment_method }}</span>
                                            </td>
                                            <td class="text-right text-bold text-green">+ ${{ number_format($payment->amount_paid, 2) }}</td>
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

        @include('layouts.footer')
    </div>

    @include('components.scripts')
</body>
</html>
