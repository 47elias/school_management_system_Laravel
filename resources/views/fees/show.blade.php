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
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-red: linear-gradient(45deg, #dd4b39, #ed5565);
            --grad-orange: linear-gradient(45deg, #f39c12, #ffcc33);
        }

        body { background-color: #f0f3f7 !important; font-family: 'Source Sans Pro', sans-serif; }

        /* Modern Box Styling */
        .box { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow: hidden;
            margin-bottom: 20px;
        }
        .box-header { padding: 18px 20px; border-bottom: 1px solid #f8fafc; }
        .box-title { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Profile Styling */
        .bg-initials-circle {
            width: 100px; height: 100px; line-height: 100px;
            margin: 0 auto 15px; font-size: 35px; font-weight: bold;
            color: #fff; background: var(--grad-blue);
            border-radius: 50%; text-align: center;
            box-shadow: 0 4px 12px rgba(0,115,183,0.3);
        }
        
        .list-group-unbordered > .list-group-item {
            border-left: none; border-right: none; border-radius: 0;
            padding: 12px 0; border-bottom: 1px solid #f1f5f9;
        }

        .balance-box {
            margin: 20px 0; padding: 25px 20px;
            background: #fff; border: 1px solid #f1f5f9; 
            border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .balance-box.overdue { background: #fff5f5; border-color: #ffe4e6; }
        
        .text-black { font-weight: 900; color: #1e293b; }
        .text-bold { font-weight: bold; }

        /* Table Vibrant */
        .table-vibrant thead th {
            background: #f8fafc; color: #64748b; text-transform: uppercase;
            font-size: 11px; padding: 12px 10px; border-bottom: 2px solid #e2e8f0 !important;
        }
        .table-vibrant td { vertical-align: middle !important; padding: 12px 10px !important; }

        /* Term Switcher Styling */
        .term-selector-container {
            display: inline-block; margin-left: 10px; vertical-align: middle;
        }
        .term-select-input {
            height: 32px; padding: 2px 12px; font-size: 13px; font-weight: 600;
            border-radius: 6px; border: 1px solid #cbd5e1; background: #fff;
            color: #475569; cursor: pointer; outline: none;
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
        }
        .print-header { display: none; margin-bottom: 30px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.layout_separator')

        <div class="content-wrapper">

            {{-- 1. DYNAMIC DATA PREPARATION --}}
            @php
                $displayTerm = $term; // Passed from Controller
                
                // Use the calculated totals dynamically from the controller/database
                $totalExpected = (float) $student->expected_total;
                $carriedForward = (float) ($student->carried_forward ?? 0);
                $currentTermFees = (float) ($termRawExpected ?? 0);
                
                $totalPaid = (float) ($termPaid ?? 0);
                $balance = (float) ($trueBalance ?? 0);
                
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
                            <p style="margin:0; font-size: 14px;">STATEMENT OF ACCOUNT - {{ strtoupper($displayTerm->term_name) }} ({{ $displayTerm->academic_year }})</p>
                        </td>
                        <td style="text-align: right;">
                            <p style="margin:0;"><strong>Date:</strong> {{ date('d M Y') }}</p>
                            <p style="margin:0;"><strong>ID:</strong> {{ $student->student_number }}</p>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Screen Header --}}
            <section class="content-header no-print" style="padding: 25px 25px 15px 25px;">
                <h1 style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        Statement: <span class="text-primary text-bold" style="color: #0073b7;">{{ $displayTerm->term_name }} {{ $displayTerm->academic_year }}</span>
                        @if($displayTerm->is_current)
                            <small class="label bg-green-active" style="font-size: 10px; margin-left: 8px; border-radius: 4px; padding: 4px 8px;">CURRENT TERM</small>
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
                    </div>

                    <div class="pull-right">
                        <button onclick="window.print()" class="btn btn-default btn-sm text-bold" style="border-radius: 6px; margin-right: 5px;">
                            <i class="fa fa-print"></i> PRINT
                        </button>
                        <a href="{{ route('fees.report') }}" class="btn btn-default btn-sm text-bold" style="border-radius: 6px;">
                            <i class="fa fa-arrow-left"></i> EXIT
                        </a>
                    </div>
                </h1>
            </section>

            <section class="content">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible no-print" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(0,166,90,0.1);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    {{-- Left Column: Summary Card --}}
                    <div class="col-md-4">
                        <div class="box box-primary" style="border-top: 4px solid #0073b7;">
                            <div class="box-body box-profile" style="padding: 25px;">
                                <div class="no-print">
                                    <div class="bg-initials-circle">
                                        {{ strtoupper(substr($student->name, 0, 1) . substr($student->surname, 0, 1)) }}
                                    </div>
                                </div>

                                <h3 class="profile-username text-center text-bold" style="color: #1e293b;">{{ $student->name }} {{ $student->surname }}</h3>
                                <p class="text-muted text-center" style="font-weight: 600;">{{ $student->student_number }} | Grade {{ $student->grade }}</p>

                                <ul class="list-group list-group-unbordered" style="margin-top: 25px;">
                                    <li class="list-group-item">
                                        <b style="color: #64748b;">Arrears B/F</b> 
                                        <span class="pull-right text-bold {{ $carriedForward > 0 ? 'text-red' : 'text-green' }}">${{ number_format($carriedForward, 2) }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b style="color: #64748b;">Term Fees (Charged)</b> 
                                        <span class="pull-right text-bold">${{ number_format($currentTermFees, 2) }}</span>
                                    </li>
                                    <li class="list-group-item" style="background: #f8fafc; padding: 12px 10px; border-radius: 6px; margin-top: 8px;">
                                        <b style="color: #1e293b;">Total Invoice</b> 
                                        <span class="pull-right text-bold text-blue">${{ number_format($totalExpected, 2) }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <b style="color: #64748b;">Total Paid (This Term)</b> 
                                        <span class="pull-right text-bold text-green">${{ number_format($totalPaid, 2) }}</span>
                                    </li>

                                    @if($hasCredit)
                                    <li class="list-group-item" style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 15px; margin-top: 10px;">
                                        <b class="text-green"><i class="fa fa-star"></i> Advance / Credit</b>
                                        <span class="pull-right text-bold text-green" style="font-size: 16px;">${{ number_format($creditAmount, 2) }}</span>
                                        <div class="text-center no-print" style="margin-top: 15px;">
                                            <button type="button" class="btn btn-sm btn-danger text-bold" style="border-radius: 6px; width: 100%;" data-toggle="modal" data-target="#deductModal">
                                                <i class="fa fa-minus-circle"></i> WITHDRAW / DEDUCT
                                            </button>
                                        </div>
                                    </li>
                                    @endif
                                </ul>

                                <div class="balance-box text-center {{ $balance > 0 ? 'overdue' : '' }}">
                                    <small class="text-uppercase text-muted" style="font-weight: 700; letter-spacing: 0.5px;">Total Outstanding Balance</small>
                                    <h2 class="{{ $balance <= 0 ? 'text-green' : 'text-red' }} text-black" style="margin: 10px 0 15px 0; font-size: 32px;">
                                        ${{ number_format($balance, 2) }}
                                    </h2>
                                    @if($balance <= 0)
                                        <div class="label label-success" style="padding: 6px 12px; border-radius: 4px; font-size: 12px;"><i class="fa fa-check-circle"></i> ACCOUNT CLEARED</div>
                                    @endif
                                </div>

                                <a href="{{ route('fees.create', ['student_id' => $student->id]) }}" class="btn btn-primary btn-block no-print text-bold" style="border-radius: 8px; padding: 12px; font-size: 14px; background: var(--grad-blue); border: none;">
                                    <i class="fa fa-plus"></i> RECORD PAYMENT
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Statement of Account / Ledger --}}
                    <div class="col-md-8">
                        <div class="box box-info" style="border-top: 4px solid #00c0ef;">
                            <div class="box-header">
                                <h3 class="box-title" style="color: #475569;"><i class="fa fa-file-text-o"></i> Statement of Account</h3>
                            </div>
                            <div class="box-body no-padding">
                                <div class="table-responsive">
                                    <table class="table table-vibrant table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description / Details</th>
                                                <th class="text-right">Charge (Dr)</th>
                                                <th class="text-right">Payment (Cr)</th>
                                                <th class="text-right">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $runningBalance = 0;
                                            @endphp

                                            {{-- 1. Balance Brought Forward --}}
                                            @if($carriedForward != 0)
                                                @php $runningBalance += $carriedForward; @endphp
                                                <tr>
                                                    <td class="text-muted">-</td>
                                                    <td class="text-bold" style="color: #1e293b;">
                                                        Balance Brought Forward {{ $carriedForward < 0 ? '(Credit)' : '(Arrears)' }}
                                                    </td>
                                                    @if($carriedForward > 0)
                                                        <td class="text-right text-red">${{ number_format($carriedForward, 2) }}</td>
                                                        <td class="text-right">-</td>
                                                    @else
                                                        <td class="text-right">-</td>
                                                        <td class="text-right text-green">${{ number_format(abs($carriedForward), 2) }}</td>
                                                    @endif
                                                    <td class="text-right text-bold">${{ number_format($runningBalance, 2) }}</td>
                                                </tr>
                                            @endif

                                            {{-- 2. Term Fees Applied --}}
                                            @if($currentTermFees > 0)
                                                @php $runningBalance += $currentTermFees; @endphp
                                                <tr>
                                                    <td class="text-muted">{{ $displayTerm->created_at ? \Carbon\Carbon::parse($displayTerm->created_at)->format('d M Y') : 'Start of Term' }}</td>
                                                    <td class="text-bold text-blue">Term Fees Charged ({{ $displayTerm->term_name }})</td>
                                                    <td class="text-right text-red">${{ number_format($currentTermFees, 2) }}</td>
                                                    <td class="text-right">-</td>
                                                    <td class="text-right text-bold">${{ number_format($runningBalance, 2) }}</td>
                                                </tr>
                                            @endif

                                            {{-- 3. Payments and Deductions (Chronological) --}}
                                            {{-- NOTE: We are intentionally hiding 'Term Invoice' here so it doesn't print twice --}}
                                            @forelse($student->payments->where('term_id', $displayTerm->id)->where('payment_method', '!=', 'Term Invoice')->sortBy('payment_date') as $payment)
                                                @php $runningBalance -= $payment->amount_paid; @endphp
                                                <tr>
                                                    <td style="color: #475569;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                                    <td>
                                                        <span style="font-weight: 600; color: #1e293b;">
                                                            {{ $payment->payment_method === 'Credit Withdrawal' ? 'Credit Withdrawal' : ($payment->amount_paid < 0 ? 'Adjustment / Reversal' : 'Payment') }} ({{ $payment->payment_method }})
                                                        </span>
                                                        @if($payment->reference_no || $payment->remarks)
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $payment->reference_no ? 'Ref: ' . $payment->reference_no : '' }} 
                                                                {{ $payment->remarks ? '- ' . $payment->remarks : '' }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    @if($payment->amount_paid < 0)
                                                        {{-- It's a debit/reversal --}}
                                                        <td class="text-right text-red">${{ number_format(abs($payment->amount_paid), 2) }}</td>
                                                        <td class="text-right">-</td>
                                                    @else
                                                        {{-- It's a normal payment credit --}}
                                                        <td class="text-right">-</td>
                                                        <td class="text-right text-green">${{ number_format($payment->amount_paid, 2) }}</td>
                                                    @endif
                                                    <td class="text-right text-bold">${{ number_format($runningBalance, 2) }}</td>
                                                </tr>
                                            @empty
                                                @if($currentTermFees == 0 && $carriedForward == 0)
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted" style="padding: 40px;">
                                                        <i class="fa fa-folder-open-o" style="font-size: 24px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                                        No financial activity recorded for this term.
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr style="background: #f8fafc;">
                                                <td colspan="4" class="text-right text-bold" style="color: #1e293b; font-size: 13px;">CLOSING BALANCE:</td>
                                                <td class="text-right text-bold {{ $runningBalance > 0 ? 'text-red' : 'text-green' }}" style="font-size: 16px;">
                                                    ${{ number_format($runningBalance, 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
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
                    <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                        <div class="modal-header" style="background: var(--grad-red); color: white;">
                            <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;"><span>&times;</span></button>
                            <h4 class="modal-title text-bold"><i class="fa fa-exclamation-triangle"></i> Deduct Credit</h4>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <input type="hidden" name="term_id" value="{{ $displayTerm->id }}">
                            <div class="form-group">
                                <label style="color: #475569;">Amount to Withdraw ($)</label>
                                <input type="number" step="0.01" name="amount" max="{{ $creditAmount }}" class="form-control" placeholder="0.00" style="border-radius: 6px;" required>
                                <p class="help-block small" style="color: #00a65a; font-weight: 600; margin-top: 8px;">Available Credit: ${{ number_format($creditAmount, 2) }}</p>
                            </div>
                            <div class="form-group">
                                <label style="color: #475569;">Reason/Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Refunded to parent" style="border-radius: 6px;"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #f1f5f9;">
                            <button type="button" class="btn btn-default pull-left text-bold" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                            <button type="submit" class="btn btn-danger text-bold" style="border-radius: 6px; background: var(--grad-red); border: none;">Confirm Deduction</button>
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