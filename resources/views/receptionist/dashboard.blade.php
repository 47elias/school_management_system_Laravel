<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Receptionist Dashboard | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        /* Enhancing AdminLTE with subtle modern touches */
        .info-box-number { font-size: 22px; font-weight: 700; }
        .btn-app-modern {
            height: auto !important;
            padding: 12px 15px !important;
            background: #fff !important;
            border: 1px solid #eee !important;
            border-radius: 4px !important;
            text-align: left !important;
            margin: 0 0 10px 0 !important;
            display: block !important;
            position: relative;
            transition: all 0.2s ease;
            color: #444 !important;
        }
        .btn-app-modern:hover {
            border-color: #3c8dbc !important;
            background: #f4f7f9 !important;
            text-decoration: none !important;
            transform: translateX(3px);
        }
        .btn-app-modern .fa { font-size: 20px !important; margin-right: 12px; float: left; line-height: 30px; }
        .btn-app-modern span { font-size: 14px; font-weight: 700; display: block; }
        .btn-app-modern small { display: block; color: #777; font-weight: normal; }

        .table-modern thead th {
            background: #fafafa;
            text-transform: uppercase;
            font-size: 11px;
            color: #888;
            border-bottom: 2px solid #f4f4f4 !important;
        }
        .label-pill { border-radius: 10px; padding: 2px 10px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        {{-- Sidebars and Topbars --}}
        @include('layouts.topbar')
        @include('layouts.receptionist_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Receptionist Dashboard
                    <small>Control Panel</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Dashboard</li>
                </ol>
            </section>

            <section class="content">
                {{-- Counters Row --}}
                <div class="row">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Enrolled</span>
                                <span class="info-box-number">{{ number_format($totalStudents) }}</span>
                                <div class="progress" style="height: 2px; margin: 5px 0;">
                                    <div class="progress-bar bg-aqua" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    <a href="{{ route('receptionist.students.index') }}" class="text-aqua">View Directory <i class="fa fa-arrow-right"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Today's Collection</span>
                                <span class="info-box-number">
                                    <small>{{ env('CURRENCY_SYMBOL', '$') }}</small> {{ number_format($todayPayments, 2) }}
                                </span>
                                <div class="progress" style="height: 2px; margin: 5px 0;">
                                    <div class="progress-bar bg-green" style="width: 100%"></div>
                                </div>
                                <span class="progress-description text-muted">
                                    Current Term Revenue
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Classes</span>
                                <span class="info-box-number">{{ $totalClasses }}</span>
                                <div class="progress" style="height: 2px; margin: 5px 0;">
                                    <div class="progress-bar bg-yellow" style="width: 100%"></div>
                                </div>
                                <span class="progress-description text-muted">
                                    Across all departments
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Left Column: Transactions --}}
                    <div class="col-md-8">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-exchange text-blue"></i> Recent Transactions</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover table-modern">
                                    <thead>
                                        <tr>
                                            <th style="padding-left: 15px;">Ref / Date</th>
                                            <th>Student</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th class="text-right" style="padding-right: 15px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPayments as $payment)
                                        <tr>
                                            <td style="padding-left: 15px; vertical-align: middle;">
                                                <small class="text-bold text-blue">{{ $payment->reference_no ?? 'REF-'.$payment->id }}</small><br>
                                                <small class="text-muted">{{ $payment->created_at->format('d M, Y') }}</small>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="text-bold">{{ $payment->student->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="text-bold">{{ number_format($payment->amount_paid, 2) }}</span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="label label-default label-pill">{{ strtoupper($payment->payment_method) }}</span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="text-green text-bold"><i class="fa fa-check"></i> PAID</span>
                                            </td>
                                            <td class="text-right" style="padding-right: 15px; vertical-align: middle;">
                                                <a href="{{ route('receptionist.payments.receipt', $payment->id) }}" target="_blank" class="btn btn-default btn-xs btn-flat">
                                                    <i class="fa fa-print"></i> Receipt
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-20 text-muted">No payments recorded today.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="box-footer text-center">
                                <a href="#" class="btn btn-sm btn-default btn-flat">View All Finance Logs</a>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Actions --}}
                    <div class="col-md-4">
                        {{-- Search Box --}}
                        <div class="box box-solid bg-blue-gradient">
                            <div class="box-header">
                                <i class="fa fa-search"></i>
                                <h3 class="box-title">Search Student</h3>
                            </div>
                            <div class="box-body">
                                <form action="{{ route('receptionist.students.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Name or Student ID...">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-default btn-flat"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Quick Links --}}
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold">Quick Tasks</h3>
                            </div>
                            <div class="box-body" style="padding-top: 15px;">
                                <a href="{{ route('receptionist.payments.create') }}" class="btn-app-modern">
                                    <i class="fa fa-plus-circle text-blue"></i>
                                    <span>New Payment</span>
                                    <small>Issue a receipt to a student</small>
                                </a>

                                <a href="{{ route('receptionist.students.create') }}" class="btn-app-modern">
                                    <i class="fa fa-user-plus text-green"></i>
                                    <span>Add Student</span>
                                    <small>Register a new admission</small>
                                </a>

                                <a href="#" class="btn-app-modern">
                                    <i class="fa fa-file-text-o text-yellow"></i>
                                    <span>Arrears List</span>
                                    <small>View students with balances</small>
                                </a>
                            </div>
                        </div>

                        {{-- System Alert --}}
                        <div class="callout callout-warning shadow-sm">
                            <h4><i class="fa fa-info-circle"></i> Digital Audit</h4>
                            <p>All payments must have a valid reference number. Never accept cash without immediate system entry.</p>
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
