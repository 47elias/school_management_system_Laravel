<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payroll Management | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Scoped Modern UI Updates (Will not affect AdminLTE Footer/Layout) -->
    <style>
        /* Modern Gradient Cards */
        .content .info-box-modern { border-radius: 12px; padding: 25px 20px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); transition: transform 0.3s ease; margin-bottom: 20px; }
        .content .info-box-modern:hover { transform: translateY(-5px); }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .content .info-box-modern .inner h3 { font-size: 36px; font-weight: 800; margin: 0 0 5px 0; letter-spacing: 1px; }
        .content .info-box-modern .inner p { font-size: 15px; margin: 0; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
        .content .info-box-modern .icon { position: absolute; right: 20px; top: 20px; font-size: 55px; opacity: 0.2; }

        /* Modern Box Styling */
        .content .box { border-radius: 12px; border-top: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; background: #ffffff; }
        .content .box-header { border-bottom: 1px solid #f1f5f9; padding: 20px 25px; background: #fff; }
        .content .box-title { font-weight: 800 !important; color: #1e293b; font-size: 18px; }
        
        /* Form Inputs & Select2 Override */
        .content .form-group label { font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .content .form-control { border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: none; padding: 10px 15px; height: auto; font-size: 15px; transition: all 0.3s ease; }
        .content .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .content .input-group-addon { border-radius: 8px 0 0 8px; border-color: #cbd5e1; background: #f8fafc; color: #64748b; }
        
        /* Select2 Modernization */
        .select2-container--default .select2-selection--single { border-radius: 8px; border: 1px solid #cbd5e1; height: 43px; padding: 6px 15px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; }
        
        /* Primary Button */
        .content .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none; border-radius: 8px; font-weight: 700; padding: 12px 20px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(59,130,246,0.3); }
        .content .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
        
        /* Table overrides */
        .content .table > tbody > tr > td { vertical-align: middle !important; padding: 16px 20px; border-top: 1px solid #f1f5f9; font-size: 15px; color: #334155; }
        .content .table > thead > tr > th { border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; padding: 16px 20px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; background: #f8fafc; }
        .content .table-hover > tbody > tr:hover { background-color: #f8fafc; }

        /* Custom Form Elements */
        .content .form-section-title { font-size: 14px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 20px; margin-top: 25px; }
        .content .form-section-title:first-child { margin-top: 0; }
        
        .content .net-preview-box { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 20px; margin-bottom: 20px; }
        .content .net-preview-box .text-xl { font-size: 28px; font-weight: 900; }
        .content .text-blue-main { color: #2563eb; }
        .content .text-red-main { color: #dc2626; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Staff Payroll
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Finance Control & Disbursement</small>
                </h1>
            </section>

            <section class="content">

                {{-- Financial Summary Row --}}
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box-modern bg-gradient-red">
                            <div class="inner">
                                <h3>${{ number_format($totalExpenses, 2) }}</h3>
                                <p>Total Salaries Paid</p>
                            </div>
                            <div class="icon"><i class="fa fa-money"></i></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="info-box-modern bg-gradient-blue">
                            <div class="inner">
                                <h3>${{ number_format($schoolBalance, 2) }}</h3>
                                <p>Available Fund Balance</p>
                            </div>
                            <div class="icon"><i class="fa fa-briefcase"></i></div>
                        </div>
                    </div>
                </div>

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif

                <div class="row">
                    {{-- Left Side: New Entry Form --}}
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-plus-circle text-blue"></i> Process New Payment</h3>
                            </div>
                            <form action="{{ route('payroll.store') }}" method="POST" id="payrollForm">
                                @csrf
                                <div class="box-body" style="padding: 25px;">
                                    <div class="form-section-title"><i class="fa fa-user"></i> 1. Staff Details</div>
                                    
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
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar-check-o"></i></span>
                                            <input type="text" name="pay_period" class="form-control" placeholder="e.g. February 2026" required>
                                        </div>
                                    </div>

                                    <div class="form-section-title"><i class="fa fa-calculator"></i> 2. Salary Breakdown</div>
                                    
                                    <div class="form-group">
                                        <label>Base Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-usd text-blue"></i></span>
                                            <input type="number" name="base_salary" id="base_salary" class="form-control" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label style="color: #10b981;">Allowances (+)</label>
                                                <input type="number" name="allowances" id="allowances" class="form-control" value="0.00" step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label style="color: #ef4444;">Deductions (-)</label>
                                                <input type="number" name="deductions" id="deductions" class="form-control" value="0.00" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="net-preview-box">
                                        <div class="row" style="display: flex; align-items: center;">
                                            <div class="col-xs-6">
                                                <span style="font-weight: 700; color: #475569; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Calculated Net:</span>
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <span id="net_preview" class="text-xl text-blue-main">$0.00</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section-title"><i class="fa fa-info-circle"></i> 3. Disbursement Info</div>
                                    
                                    <div class="form-group">
                                        <label>Date of Payment</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>Internal Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Bank transfer, cash, etc."></textarea>
                                    </div>
                                </div>
                                <div class="box-footer" style="padding: 20px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                                        <i class="fa fa-file-text-o"></i> GENERATE PAYSLIP
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Right Side: History --}}
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-history text-blue"></i> Recent Transactions</h3>
                            </div>
                            <div class="box-body no-padding table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 120px;">Date</th>
                                            <th>Staff Information</th>
                                            <th>Period</th>
                                            <th>Net Salary</th>
                                            <th class="text-right" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payslips as $p)
                                        <tr>
                                            <td>
                                                <span style="color: #64748b; font-weight: 600;">
                                                    {{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: #1e293b; font-size: 15px;">{{ $p->user->name }}</div>
                                                <small style="color: #64748b; font-weight: 600;"><i class="fa fa-id-card-o"></i> {{ $p->user->employee_number ?? 'No ID' }}</small>
                                            </td>
                                            <td>
                                                <span class="label" style="background: #e2e8f0; color: #475569; font-size: 13px; padding: 5px 10px;">{{ $p->pay_period }}</span>
                                            </td>
                                            <td>
                                                <strong style="font-size: 16px; color: #10b981;">${{ number_format($p->net_salary, 2) }}</strong>
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('payroll.print', $p->id) }}" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #3b82f6; border-radius: 6px; margin-right: 5px;" title="Print">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <form action="{{ route('payroll.destroy', $p->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #ef4444; border-radius: 6px;" onclick="return confirm('Delete this payroll record?');" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 60px 20px;">
                                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                    <i class="fa fa-file-text-o" style="font-size: 35px; color: #94a3b8;"></i>
                                                </div>
                                                <h4 style="font-weight: 700; color: #475569;">No Payroll Records Found</h4>
                                                <p style="color: #94a3b8;">Select a staff member and generate their first payslip.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($payslips->hasPages())
                            <div class="box-footer" style="background: #fff; border-radius: 0 0 12px 12px; border-top: 1px solid #f1f5f9;">
                                <div class="pull-right">
                                    {{ $payslips->links() }}
                                </div>
                            </div>
                            @endif
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