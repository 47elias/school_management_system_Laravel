<!DOCTYPE html>
<html>
<head>
    <title>Collect Fees | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')

    {{-- Select2 Modern Theme --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    {{-- Datepicker CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <style>
        /* Modern AdminLTE Tweaks */
        .payment-box { border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #00a65a; }

        /* Select2 Search Optimization */
        .select2-container--default .select2-selection--single {
            height: 45px !important;
            padding: 8px 12px;
            border-color: #d2d6de !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
        }

        /* Verification Card - Modern Blue Look */
        #details_card {
            display: none;
            background: #f4fbff;
            border: 1px solid #cde4f7;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0 25px 0;
        }
        .balance-amount { font-size: 32px; font-weight: 900; color: #dd4b39; }
        .student-name-display { font-size: 22px; font-weight: bold; color: #2c3b41; text-transform: uppercase; }
        .section-divider { border-bottom: 1px solid #eee; margin: 20px 0; position: relative; }
        .section-divider span { background: #fff; padding: 0 10px; position: absolute; top: -10px; left: 20px; color: #999; font-size: 11px; text-transform: uppercase; font-weight: bold; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.receptionist_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Fee Collection <small>Record New Payment</small></h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">

                        {{-- Success Alert --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}

                                @if(session('payment_id'))
                                    <a href="{{ route('receptionist.payments.receipt', session('payment_id')) }}" target="_blank" class="btn btn-default btn-sm" style="margin-left:15px; text-decoration:none; color:#333;">
                                        <i class="fa fa-print"></i> PRINT RECEIPT
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- General Error Alert (e.g. Database Exceptions) --}}
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="box box-success payment-box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-money"></i> Payment Details</h3>
                            </div>

                            <form action="{{ route('receptionist.payments.store') }}" method="POST" id="paymentForm">
                                @csrf
                                <div class="box-body" style="padding: 25px;">

                                    {{-- Step 1: Student Search --}}
                                    <div class="form-group {{ $errors->has('student_id') ? 'has-error' : '' }}">
                                        <label>Search Student (Type Name or ID Number)</label>
                                        <select name="student_id" id="student_select" class="form-control select2" style="width: 100%;" required>
                                            <option value=""></option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}"
                                                        {{ old('student_id') == $student->id ? 'selected' : '' }}
                                                        data-number="{{ $student->student_number }}"
                                                        data-fullname="{{ $student->name }} {{ $student->surname }}"
                                                        data-classname="{{ $student->grade ?? 'No Grade' }}"
                                                        data-arrears="{{ number_format($student->calculated_balance ?? 0, 2) }}">
                                                    {{ $student->student_number }} — {{ $student->name }} {{ $student->surname }} ({{ $student->grade }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('student_id')) <span class="help-block">{{ $errors->first('student_id') }}</span> @endif
                                    </div>

                                    {{-- Dynamic Verification Card --}}
                                    <div id="details_card">
                                        <div class="row">
                                            <div class="col-sm-7" style="border-right: 1px solid #d2e4f3;">
                                                <small class="text-primary"><strong>VERIFIED STUDENT</strong></small>
                                                <div class="student-name-display" id="view_fullname"></div>
                                                <p class="text-muted" style="margin-top: 5px;">
                                                    <strong>ID:</strong> <span id="view_number" class="text-blue"></span> |
                                                    <strong>Grade:</strong> <span id="view_class"></span>
                                                </p>
                                            </div>
                                            <div class="col-sm-5 text-right">
                                                <small class="text-danger"><strong>TOTAL BALANCE DUE</strong></small>
                                                <div class="balance-amount">$<span id="view_arrears">0.00</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-divider"><span>Payment Info</span></div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('term_id') ? 'has-error' : '' }}">
                                                <label>Academic Term</label>
                                                <select name="term_id" class="form-control" required>
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term->id }}" {{ (old('term_id') == $term->id || ($term->is_current && !old('term_id'))) ? 'selected' : '' }}>
                                                            {{ $term->term_name }} ({{ $term->academic_year }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if($errors->has('term_id')) <span class="help-block">{{ $errors->first('term_id') }}</span> @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('payment_date') ? 'has-error' : '' }}">
                                                <label>Date of Payment</label>
                                                <div class="input-group date">
                                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                    <input type="text" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', date('d/m/Y')) }}" required autocomplete="off">
                                                </div>
                                                @if($errors->has('payment_date')) <span class="help-block">{{ $errors->first('payment_date') }}</span> @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('amount_paid') ? 'has-error' : '' }}">
                                                <label>Amount Paid (USD)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" style="font-weight: bold;">$</span>
                                                    <input type="number" name="amount_paid" id="amount_input" class="form-control input-lg" style="font-weight: bold;" value="{{ old('amount_paid') }}" placeholder="0.00" step="0.01" required>
                                                </div>
                                                @if($errors->has('amount_paid')) <span class="help-block">{{ $errors->first('amount_paid') }}</span> @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <select name="payment_method" class="form-control input-lg" required>
                                                    @foreach(['Cash', 'Bank Transfer', 'EcoCash/Mobile Money', 'Cheque', 'Swipe/Card'] as $method)
                                                        <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->has('reference_no') ? 'has-error' : '' }}">
                                        <label>Reference Number</label>
                                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="e.g. TXN-123456">
                                        @if($errors->has('reference_no')) <span class="help-block">{{ $errors->first('reference_no') }}</span> @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks / Notes</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Internal notes...">{{ old('remarks') }}</textarea>
                                    </div>
                                </div>

                                <div class="box-footer" style="padding: 15px 25px;">
                                    <button type="submit" class="btn btn-success btn-lg btn-flat pull-right" style="padding: 10px 30px; font-weight: bold;">
                                        <i class="fa fa-save"></i> SAVE PAYMENT
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#student_select').select2({
                placeholder: "Type Name, Surname, or ID...",
                allowClear: true,
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    if (typeof data.text === 'undefined') return null;

                    var term = params.term.toLowerCase();
                    var name = $(data.element).data('fullname').toLowerCase();
                    var id = $(data.element).data('number').toLowerCase();

                    if (name.indexOf(term) > -1 || id.indexOf(term) > -1) {
                        return data;
                    }
                    return null;
                }
            });

            // Open on focus
            $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
                $(this).closest(".select2-container").siblings('select:enabled').select2('open');
            });

            // Datepicker
            $('#payment_date').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Dynamic UI Update
            function updateCard() {
                const sel = $('#student_select').find(':selected');
                if(sel.val() != "") {
                    $('#view_number').text(sel.data('number'));
                    $('#view_fullname').text(sel.data('fullname'));
                    $('#view_class').text(sel.data('classname'));
                    $('#view_arrears').text(sel.data('arrears'));

                    // Only auto-fill amount if it's empty (preserves user input on validation fail)
                    if($('#amount_input').val() == "") {
                        const bal = sel.data('arrears').toString().replace(/,/g, '');
                        $('#amount_input').val(bal);
                    }

                    $('#details_card').slideDown(300);
                } else {
                    $('#details_card').slideUp(200);
                }
            }

            $('#student_select').on('change', updateCard);

            // Trigger card update on load (for old input values after validation failure)
            if($('#student_select').val() != "") {
                updateCard();
            }
        });
    </script>
</body>
</html>
