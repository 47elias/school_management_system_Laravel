<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt_{{ $payment->reference_no }}</title>
    @include('components.adminlte')
    <style>
        /* Print-specific adjustments */
        @media print {
            .no-print { display: none !important; }
            .receipt-border { border: none !important; margin: 0 !important; padding: 0 !important; }
        }
        .receipt-border {
            border: 2px solid #eee;
            padding: 30px;
            max-width: 850px;
            margin: 20px auto;
            background: #fff;
        }
        .text-black { color: #000 !important; font-weight: 700; }
        .table-summary td { vertical-align: middle; }
    </style>
</head>
<body onload="window.print();">
    <div class="receipt-border">
        {{-- Header Section --}}
        <div class="row">
            <div class="col-xs-12">
                <h2 class="page-header" style="border-bottom: 2px solid #3c8dbc; padding-bottom: 10px;">
                    <i class="fa fa-university text-primary"></i> {{ env('SCHOOL_NAME', 'School Management System') }}
                    <small class="pull-right" style="margin-top: 10px;">Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</small>
                </h2>
            </div>
        </div>

        {{-- Info Section --}}
        <div class="row invoice-info" style="margin-top: 20px;">
            <div class="col-sm-4 invoice-col">
                <span class="text-muted">FROM</span>
                <address>
                    <strong>Accounts Office</strong><br>
                    Phone: (263) 123-4567<br>
                    Email: finance@school.com
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <span class="text-muted">STUDENT</span>
                <address>
                    {{-- Fixed the missing variable issue here --}}
                    <strong class="text-black">{{ $student->name }} {{ $student->surname }}</strong><br>
                    ID: <span class="label label-default">{{ $student->student_number }}</span><br>
                    Grade: {{ $student->grade }}
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <div style="background: #f9f9f9; padding: 10px; border-radius: 5px;">
                    <b>Receipt #{{ $payment->reference_no }}</b><br>
                    <b>Term:</b> {{ $payment->term->term_name ?? 'N/A' }}<br>
                    <b>Method:</b> {{ ucfirst($payment->payment_method) }}
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="row" style="margin-top: 30px;">
            <div class="col-xs-12 table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th>Description</th>
                            <th class="text-right">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>School Fees Payment</strong><br>
                                <small class="text-muted">{{ $payment->remarks ?? 'Standard Tuition/Monthly Installment' }}</small>
                            </td>
                            <td class="text-right text-black" style="font-size: 1.2em;">
                                ${{ number_format($payment->amount_paid, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary Section --}}
        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-6">
                <p class="lead">Payment Status:</p>
                <div class="well well-sm no-shadow" style="background: #fdfdfd; border-left: 5px solid #00a65a;">
                    <p>Thank you for your payment. Your school account has been credited successfully.</p>
                </div>
                <div style="margin-top: 20px;">
                    <p>__________________________</p>
                    <p>Accounts Signature</p>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="table-responsive">
                    <table class="table table-summary">
                        <tr>
                            <th style="width:50%">Total Paid:</th>
                            <td class="text-right text-bold">${{ number_format($payment->amount_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Balance Remaining:</th>
                            <td class="text-right text-red text-black" style="font-size: 1.3em;">
                                ${{ number_format($student->calculated_balance, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Actions Section --}}
        <div class="row no-print" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
            <div class="col-xs-12 text-center">
                <button onclick="window.print();" class="btn btn-lg btn-success btn-flat">
                    <i class="fa fa-print"></i> PRINT RECEIPT
                </button>
                <a href="{{ route('receptionist.payments.index') }}" class="btn btn-lg btn-default btn-flat">
                    BACK TO PAYMENTS
                </a>
            </div>
        </div>
    </div>
</body>
</html>
