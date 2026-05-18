<!DOCTYPE html>
<html>
<head>
    <title>Payroll Management | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')

    <style>
        /* Custom Styles for Offline Compatibility */
        .salary-badge { font-size: 1.1em; font-weight: bold; }
        .table-v-align td { vertical-align: middle !important; }
        .form-section-title {
            padding: 10px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
            color: #3c8dbc;
        }

        /* Replaced Tailwind Utility Classes with Custom CSS */
        .flex-container { display: flex; align-items: center; }
        .card-summary {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 5px solid #d2d6de;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .border-red { border-left-color: #dd4b39 !important; }
        .border-blue { border-left-color: #0073b7 !important; }
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .bg-red-light { background-color: #f9f0f0; color: #dd4b39; }
        .bg-blue-light { background-color: #f0f4f9; color: #0073b7; }
        .net-preview-box {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .text-xl { font-size: 24px; }
        .font-black { font-weight: 900; }
        .text-blue-main { color: #0073b7; }
        .text-red-main { color: #dd4b39; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Staff Payroll <small>Finance Control</small></h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Payroll</li>
                </ol>
            </section>

            <section class="content">

                {{-- Financial Summary Row --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="card-summary border-red">
                            <div class="flex-container">
                                <div class="icon-circle bg-red-light">
                                    <i class="fa fa-money fa-2x"></i>
                                </div>
                                <div>
                                    <p style="margin:0; color: #777; font-size: 12px; text-transform: uppercase;">Total Salaries Paid</p>
                                    <p style="margin:0; font-size: 24px; font-weight: bold;">${{ number_format($totalExpenses, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-summary border-blue">
                            <div class="flex-container">
                                <div class="icon-circle bg-blue-light">
                                    <i class="fa fa-briefcase fa-2x"></i>
                                </div>
                                <div>
                                    <p style="margin:0; color: #777; font-size: 12px; text-transform: uppercase;">Available Fund Balance</p>
                                    <p style="margin:0; font-size: 24px; font-weight: bold; color: #0073b7;">${{ number_format($schoolBalance, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    {{-- Left Side: New Entry Form --}}
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-plus-circle"></i> Process New Payment</h3>
                            </div>
                            <form action="{{ route('payroll.store') }}" method="POST" id="payrollForm">
                                @csrf
                                <div class="box-body">
                                    <div class="form-section-title">Staff Details</div>
                                    <div class="form-group">
                                        <label>Select Staff Member</label>
                                        <select name="user_id" id="teacher_id" class="form-control select2" style="width: 100%;" required>
                                            <option value="">-- Search Teacher/Staff --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}"
                                                        data-salary="{{ $teacher->base_salary }}"
                                                        data-empno="{{ $teacher->employee_number }}">
                                                    {{ $teacher->name }} ({{ $teacher->employee_number ?? 'No ID' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Pay Period (Month/Year)</label>
                                        <input type="text" name="pay_period" class="form-control" placeholder="e.g. February 2026" required>
                                    </div>

                                    <div class="form-section-title">Salary Breakdown</div>
                                    <div class="form-group">
                                        <label>Base Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                            <input type="number" name="base_salary" id="base_salary" class="form-control" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label class="text-green">Allowances (+)</label>
                                                <input type="number" name="allowances" id="allowances" class="form-control" value="0.00" step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label class="text-red">Deductions (-)</label>
                                                <input type="number" name="deductions" id="deductions" class="form-control" value="0.00" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="net-preview-box">
                                        <div class="row">
                                            <div class="col-xs-6"><span class="font-bold">Calculated Net:</span></div>
                                            <div class="col-xs-6 text-right">
                                                <span id="net_preview" class="text-xl font-black text-blue-main">$0.00</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Date of Payment</label>
                                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Internal Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Bank transfer, cash, etc."></textarea>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block btn-lg btn-flat">
                                        <i class="fa fa-save"></i> Generate & Post Payslip
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Right Side: History --}}
                    <div class="col-md-8">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-history"></i> Recent Transactions</h3>
                            </div>
                            <div class="box-body no-padding table-responsive">
                                <table class="table table-hover table-v-align table-striped">
                                    <thead>
                                        <tr style="background-color: #f4f4f4;">
                                            <th style="width: 110px;">Date</th>
                                            <th>Staff Information</th>
                                            <th>Period</th>
                                            <th>Net Salary</th>
                                            <th class="text-center" style="width: 140px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payslips as $p)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                                            <td>
                                                <div style="font-weight: bold; color: #333;">{{ $p->user->name }}</div>
                                                <small class="text-muted"><i class="fa fa-id-card"></i> {{ $p->user->employee_number ?? 'No ID' }}</small>
                                            </td>
                                            <td><span class="label label-default">{{ $p->pay_period }}</span></td>
                                            <td>
                                                <span class="text-blue-main salary-badge">${{ number_format($p->net_salary, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('payroll.print', $p->id) }}" target="_blank" class="btn btn-sm btn-default" title="Print">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <form action="{{ route('payroll.destroy', $p->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 50px; color: #999;">
                                                <i class="fa fa-info-circle fa-3x" style="margin-bottom: 10px;"></i><br> No payroll records found.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="box-footer clearfix text-center">
                                {{ $payslips->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    @include('components.scripts')

    <script>
        $(document).ready(function() {
            // Initialize Select2 (Assuming it's included in your components.scripts)
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "-- Search Staff Member --",
                    allowClear: true
                });
            }

            function calculateNet() {
                let base = parseFloat($('#base_salary').val()) || 0;
                let allowances = parseFloat($('#allowances').val()) || 0;
                let deductions = parseFloat($('#deductions').val()) || 0;
                let net = (base + allowances) - deductions;

                $('#net_preview').text('$' + net.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                if(net < 0) {
                    $('#net_preview').addClass('text-red-main').removeClass('text-blue-main');
                } else {
                    $('#net_preview').addClass('text-blue-main').removeClass('text-red-main');
                }
            }

            $('#teacher_id').on('change', function() {
                const selected = $(this).find(':selected');
                const salary = selected.data('salary');
                $('#base_salary').val(salary ? parseFloat(salary).toFixed(2) : '0.00');
                calculateNet();
            });

            $('input[type="number"]').on('input', calculateNet);
        });
    </script>
</body>
</html>
