<!DOCTYPE html>
<html>
<head>
    <title>Collect Fees | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    {{-- Add Datepicker CSS if not in adminlte component --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <style>
        /* --- Fee collection page refresh --- */
        .fee-page .box {
            border-top: 3px solid #00a65a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .fee-page .box-title {
            font-weight: 600;
            letter-spacing: .2px;
        }
        .fee-page .nav-tabs-custom {
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .fee-page .nav-tabs-custom > .nav-tabs > li.active > a {
            font-weight: 600;
        }
        .fee-page .nav-tabs-custom > .nav-tabs > li > a .fa {
            margin-right: 6px;
        }
        .fee-page .method-card {
            border: 2px solid #e8e8e8;
            border-radius: 6px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all .15s ease-in-out;
            margin-bottom: 15px;
            background: #fff;
        }
        .fee-page .method-card:hover {
            border-color: #b7dcc4;
        }
        .fee-page .method-card.selected {
            border-color: #00a65a;
            background: #f4fbf7;
            box-shadow: 0 0 0 1px #00a65a inset;
        }
        .fee-page .method-card .fa {
            font-size: 22px;
            display: block;
            margin-bottom: 6px;
            color: #444;
        }
        .fee-page .method-card.selected .fa {
            color: #00a65a;
        }
        .fee-page .method-card span {
            font-size: 12.5px;
            font-weight: 600;
            color: #333;
        }
        .fee-page .online-note {
            background: #f4f9ff;
            border: 1px solid #d9e8fb;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 12.5px;
            color: #5a6b7d;
        }
        .fee-page .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #444;
        }
        .fee-page .help-block.small {
            margin-top: 4px;
        }
        .fee-page .pay-online-btn {
            font-weight: 600;
            letter-spacing: .3px;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper fee-page">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Fee Collection <small>Record a cash payment or send a student an online payment link</small></h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-9 col-md-offset-0" style="margin: 0 auto; float: none;">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('online_success'))
                            <div class="alert alert-info alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-paper-plane"></i> Payment link sent!</h4>
                                {{ session('online_success') }}
                            </div>
                        @endif

                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab-cash" data-toggle="tab">
                                        <i class="fa fa-money"></i> Cash / Manual Entry
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-online" data-toggle="tab">
                                        <i class="fa fa-credit-card"></i> Pay Online
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">

                                {{-- =========================================================
                                     TAB 1: CASH / MANUAL ENTRY — UNCHANGED LOGIC, restyled only
                                ========================================================== --}}
                                <div class="tab-pane active" id="tab-cash">
                                    <form action="{{ route('fees.store') }}" method="POST">
                                        @csrf
                                        <div class="box-body" style="padding-top: 20px;">
                                            <div class="form-group {{ $errors->has('student_id') ? 'has-error' : '' }}">
                                                <label>Search Student</label>
                                                <select name="student_id" class="form-control select2" style="width: 100%;" required>
                                                    <option value="">-- Select Student --</option>
                                                    @foreach($students as $student)
                                                        <option value="{{ $student->id }}">
                                                            {{ $student->student_number }} - {{ $student->name }} {{ $student->surname }} ({{ $student->grade }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Academic Term</label>
                                                        <select name="term_id" class="form-control" required>
                                                            @foreach($terms as $term)
                                                                <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                                                    {{ $term->term_name }} ({{ $term->academic_year }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Date of Payment (DD/MM/YYYY)</label>
                                                        <div class="input-group date">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            {{-- Changed to type="text" to allow custom formatting --}}
                                                            <input type="text" name="payment_date" id="payment_date" class="form-control" value="{{ date('d/m/Y') }}" required autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Amount Paid</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon">$</span>
                                                            <input type="number" name="amount_paid" class="form-control" placeholder="0.00" step="0.01" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Payment Method</label>
                                                        <select name="payment_method" class="form-control" required>
                                                            <option value="Cash">Cash</option>
                                                            <option value="Bank Transfer">Bank Transfer</option>
                                                            <option value="EcoCash/Mobile Money">EcoCash/Mobile Money</option>
                                                            <option value="Cheque">Cheque</option>
                                                            <option value="Swipe/Card">Swipe/Card</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Reference Number (Receipt/Transaction ID)</label>
                                                <input type="text" name="reference_no" class="form-control" placeholder="e.g. TXN-123456">
                                            </div>

                                            <div class="form-group">
                                                <label>Remarks / Optional Notes</label>
                                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter any specific details about this payment..."></textarea>
                                                <p class="help-block small text-muted">These notes will be saved to the database and visible on the student statement.</p>
                                            </div>
                                        </div>

                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-success btn-flat pull-right">
                                                <i class="fa fa-save"></i> Save Payment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                {{-- END TAB 1 --}}

                                {{-- =========================================================
                                     TAB 2: PAY ONLINE — new
                                     Posts to a new route (fees.payOnline) that your controller
                                     should implement to create a Paynow (or other gateway)
                                     transaction and redirect the payer, or email/SMS them a
                                     payment link. This does not touch the cash flow above.
                                ========================================================== --}}
                                <div class="tab-pane" id="tab-online">
                                    <form action="{{ route('fees.payOnline') }}" method="POST" id="onlinePayForm">
                                        @csrf
                                        <div class="box-body" style="padding-top: 20px;">

                                            <div class="online-note" style="margin-bottom: 18px;">
                                                <i class="fa fa-info-circle"></i>
                                                Generates a secure payment link the parent can pay via <strong>EcoCash, OneMoney, Visa or Mastercard</strong>.
                                                You can send it straight to their phone/email, or copy it to share yourself.
                                            </div>

                                            <div class="form-group">
                                                <label>Search Student</label>
                                                <select name="student_id" class="form-control select2-online" style="width: 100%;" required>
                                                    <option value="">-- Select Student --</option>
                                                    @foreach($students as $student)
                                                        <option value="{{ $student->id }}" data-parent-phone="{{ $student->parent_phone ?? '' }}" data-parent-email="{{ $student->parent_email ?? '' }}">
                                                            {{ $student->student_number }} - {{ $student->name }} {{ $student->surname }} ({{ $student->grade }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Academic Term</label>
                                                        <select name="term_id" class="form-control" required>
                                                            @foreach($terms as $term)
                                                                <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                                                    {{ $term->term_name }} ({{ $term->academic_year }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Amount Due</label>
                                                        <div class="input-group">
                                                            <span class="input-group-addon">$</span>
                                                            <input type="number" name="amount_paid" class="form-control" placeholder="0.00" step="0.01" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <label>Pay Via</label>
                                            <input type="hidden" name="online_channel" id="online_channel" value="paynow_link" required>
                                            <div class="row">
                                                <div class="col-xs-4">
                                                    <div class="method-card selected" data-channel="paynow_link">
                                                        <i class="fa fa-link"></i>
                                                        <span>Payment Link</span>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4">
                                                    <div class="method-card" data-channel="ecocash_push">
                                                        <i class="fa fa-mobile"></i>
                                                        <span>EcoCash Push (USSD)</span>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4">
                                                    <div class="method-card" data-channel="card">
                                                        <i class="fa fa-credit-card"></i>
                                                        <span>Card (Visa/Mastercard)</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Parent/Payer Mobile Number</label>
                                                        <input type="text" name="payer_phone" id="payer_phone" class="form-control" placeholder="e.g. 077XXXXXXX">
                                                        <p class="help-block small text-muted">Required for EcoCash push; optional otherwise (used for SMS notification).</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Parent/Payer Email</label>
                                                        <input type="email" name="payer_email" id="payer_email" class="form-control" placeholder="parent@example.com">
                                                        <p class="help-block small text-muted">Payment link and receipt will be emailed here.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Remarks / Optional Notes</label>
                                                <textarea name="remarks" class="form-control" rows="2" placeholder="Enter any specific details about this payment..."></textarea>
                                            </div>
                                        </div>

                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-primary btn-flat pull-right pay-online-btn">
                                                <i class="fa fa-paper-plane"></i> Generate & Send Payment Link
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                {{-- END TAB 2 --}}

                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    {{-- Add Datepicker JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 on both tabs
            $('.select2, .select2-online').select2({
                placeholder: "Type student name or ID...",
                allowClear: true
            });

            // Initialize Datepicker with DD/MM/YYYY format (cash tab only)
            $('#payment_date').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Online tab: payment method card selector
            $('.method-card').on('click', function() {
                $('.method-card').removeClass('selected');
                $(this).addClass('selected');
                $('#online_channel').val($(this).data('channel'));

                // EcoCash push requires a phone number
                if ($(this).data('channel') === 'ecocash_push') {
                    $('#payer_phone').prop('required', true);
                } else {
                    $('#payer_phone').prop('required', false);
                }
            });

            // Online tab: prefill payer phone/email from selected student, if available
            $('.select2-online').on('select2:select', function(e) {
                var opt = e.params.data.element;
                var phone = $(opt).data('parent-phone');
                var email = $(opt).data('parent-email');
                if (phone && !$('#payer_phone').val()) { $('#payer_phone').val(phone); }
                if (email && !$('#payer_email').val()) { $('#payer_email').val(email); }
            });
        });
    </script>
</body>
</html>
