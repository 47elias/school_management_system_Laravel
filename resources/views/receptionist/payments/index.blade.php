<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Payment History | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        /* Maintaining your preferred modern table look within AdminLTE */
        .table-modern thead th {
            background-color: #f4f7f9;
            color: #333;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #d2d6de !important;
        }
        .receipt-number {
            font-family: 'Source Code Pro', monospace;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        .text-bold { font-weight: bold; }
        .text-green { color: #00a65a !important; }
        /* Flex utility for AdminLTE boxes */
        .flex-header { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.receptionist_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Payment History
                    <small>Transaction Audit Trail</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Payments</li>
                </ol>
            </section>

            <section class="content">
                <div class="box box-primary">
                    <div class="box-header with-border flex-header">
                        <h3 class="box-title"><i class="fa fa-history"></i> Recent Transactions</h3>
                        <div class="box-tools">
                            <a href="{{ route('receptionist.students.index') }}" class="btn btn-sm btn-success btn-flat">
                                <i class="fa fa-plus"></i> RECORD NEW PAYMENT
                            </a>
                        </div>
                    </div>

                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover table-modern">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Date & Time</th>
                                        <th>Receipt #</th>
                                        <th>Student Name</th>
                                        <th>Term</th>
                                        <th>Method</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-right" style="padding-right: 15px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td class="text-muted">
                                                {{ $payment->created_at->format('d M, Y') }}<br>
                                                <small>{{ $payment->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <span class="receipt-number">
                                                    REF-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-uppercase" style="color: #3c8dbc;">
                                                    {{ $payment->student->surname ?? 'N/A' }}
                                                </strong>,
                                                {{ $payment->student->name ?? '' }}
                                            </td>
                                            <td>
                                                {{ $payment->term->term_name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="label label-info shadow-sm" style="text-transform: uppercase;">
                                                    {{ $payment->payment_method ?? 'Cash' }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <span class="text-bold text-green" style="font-size: 16px;">
                                                    ${{ number_format($payment->amount_paid, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-right" style="padding-right: 15px;">
                                                <div class="btn-group">
                                                    <a href="{{ route('receptionist.payments.print', $payment->id) }}"
                                                       target="_blank"
                                                       class="btn btn-default btn-xs btn-flat"
                                                       title="Print Receipt">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <a href="{{ route('receptionist.students.show', $payment->student_id) }}"
                                                       class="btn btn-primary btn-xs btn-flat"
                                                       title="View Statement">
                                                        <i class="fa fa-user"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center" style="padding: 60px 0;">
                                                <i class="fa fa-folder-open-o" style="font-size: 40px; color: #d2d6de; display: block; margin-bottom: 10px;"></i>
                                                <p class="text-muted">No payment records found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($payments->hasPages())
                        <div class="box-footer clearfix">
                            <div class="pull-left" style="margin-top: 5px;">
                                <span class="text-sm text-muted">
                                    Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} records
                                </span>
                            </div>
                            <div class="pull-right">
                                {{ $payments->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
