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
            border-radius: 8px;
        }
        .fee-page .box-title {
            font-weight: 600;
            letter-spacing: .2px;
        }
        .fee-page .nav-tabs-custom {
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        .fee-page .nav-tabs-custom > .nav-tabs > li.active > a {
            font-weight: 600;
            border-top-color: #00a65a;
        }
        .fee-page .nav-tabs-custom > .nav-tabs > li > a .fa {
            margin-right: 6px;
        }
        .fee-page .method-card {
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            padding: 16px 10px;
            text-align: center;
            cursor: pointer;
            transition: all .2s ease-in-out;
            margin-bottom: 15px;
            background: #fff;
        }
        .fee-page .method-card:hover {
            border-color: #b7dcc4;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .fee-page .method-card.selected {
            border-color: #00a65a;
            background: #f4fbf7;
            box-shadow: 0 0 0 1px #00a65a inset;
        }
        .fee-page .method-card .fa {
            font-size: 26px;
            display: block;
            margin-bottom: 8px;
            color: #666;
            transition: 0.2s;
        }
        .fee-page .method-card.selected .fa {
            color: #00a65a;
            transform: scale(1.1);
        }
        .fee-page .method-card span {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            display: block;
        }
        
        /* Specific Method Colors (Optional, if you want them colorful even when not selected) */
        .method-card[data-method="EcoCash"] .fa, .method-card[data-channel="ecocash_push"] .fa { color: #009be3; } /* EcoCash Blue */
        .method-card[data-method="ZIPIT"] .fa { color: #f39c12; } /* ZIPIT Orange/Yellow */
        .method-card[data-method="Card"] .fa, .method-card[data-channel="card"] .fa { color: #dd4b39; } /* Card Red */

        .fee-page .online-note {
            background: #f4f9ff;
            border: 1px solid #d9e8fb;
            border-radius: 6px;
            padding: 12px 15px;
            font-size: 13px;
            color: #5a6b7d;
        }
        .fee-page .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #444;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.layout_separator')

    <div class="wrapper fee-page">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Fee Collection <small>Record a manual payment or send an online payment link</small></h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-9" style="margin: 0 auto; float: none;">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" style="border-radius: 6px;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('online_success'))
                            <div class="alert alert-info alert-dismissible" style="border-radius: 6px;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-paper-plane"></i> Link Sent / Processing</h4>
                                {{ session('online_success') }}
                            </div>
                        @endif

                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab-cash" data-toggle="tab">
                                        <i class="fa fa-handshake-o"></i> Record Direct Payment
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-online" data-toggle="tab">
                                        <i class="fa fa-globe"></i> Send Online Link
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">

                                {{-- =========================================================
                                     TAB 1: DIRECT PAYMENT ENTRY (Updated with Icons)
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
                                                        <select name="term_id" class="form-control" required style="border-radius: 4px;">
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
                                                        <label>Date of Payment</label>
                                                        <div class="input-group date">
                                                            <div class="input-group-addon" style="border-radius: 4px 0 0 4px;">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <input type="text" name="payment_date" id="payment_date" class="form-control" value="{{ date('d/m/Y') }}" required autocomplete="off" style="border-radius: 0 4px 4px 0;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Amount Paid</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon" style="border-radius: 4px 0 0 4px; font-weight: bold;">$</span>
                                                    <input type="number" name="amount_paid" class="form-control" placeholder="0.00" step="0.01" required style="border-radius: 0 4px 4px 0; font-size: 16px;">
                                                </div>
                                            </div>

                                            {{-- New Visual Method Selector --}}
                                            <div class="form-group">
                                                <label>Payment Method</label>
                                                <input type="hidden" name="payment_method" id="direct_payment_method" value="Cash" required>
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-3">
                                                        <div class="method-card direct-method-card selected" data-method="Cash">
                                                            <i class="fa fa-money"></i>
                                                            <span>Physical Cash</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-md-3">
                                                        <div class="method-card direct-method-card" data-method="EcoCash">
                                                            <i class="fa fa-mobile-phone"></i>
                                                            <span>EcoCash</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-md-3">
                                                        <div class="method-card direct-method-card" data-method="ZIPIT">
                                                            <i class="fa fa-exchange"></i>
                                                            <span>ZIPIT / Bank</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-md-3">
                                                        <div class="method-card direct-method-card" data-method="Card">
                                                            <i class="fa fa-credit-card"></i>
                                                            <span>Master/Visa</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Reference Number (Receipt / Approval Code)</label>
                                                <input type="text" name="reference_no" class="form-control" placeholder="e.g. TXN-123456" style="border-radius: 4px;">
                                            </div>

                                            <div class="form-group">
                                                <label>Remarks / Notes</label>
                                                <textarea name="remarks" class="form-control" rows="2" placeholder="Specific details about this payment..." style="border-radius: 4px;"></textarea>
                                            </div>
                                        </div>

                                        <div class="box-footer" style="border-radius: 0 0 8px 8px;">
                                            <button type="submit" class="btn btn-success btn-flat pull-right" style="border-radius: 4px; font-weight: bold; padding: 8px 20px;">
                                                <i class="fa fa-save"></i> Save Payment Record
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                {{-- =========================================================
                                     TAB 2: PAY ONLINE (Remote Links)
                                ========================================================== --}}
                                <div class="tab-pane" id="tab-online">
                                    <form action="{{ route('fees.payOnline') }}" method="POST" id="onlinePayForm">
                                        @csrf
                                        <div class="box-body" style="padding-top: 20px;">

                                            <div class="online-note" style="margin-bottom: 20px;">
                                                <i class="fa fa-info-circle text-blue"></i>
                                                Generates a remote payment link the parent can pay from home via <strong>EcoCash, OneMoney, or Card</strong>.
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
                                                        <select name="term_id" class="form-control" required style="border-radius: 4px;">
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
                                                            <span class="input-group-addon" style="border-radius: 4px 0 0 4px; font-weight: bold;">$</span>
                                                            <input type="number" name="amount_paid" class="form-control" placeholder="0.00" step="0.01" required style="border-radius: 0 4px 4px 0; font-size: 16px;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Remote Payment Channel</label>
                                                <input type="hidden" name="online_channel" id="online_channel" value="paynow_link" required>
                                                <div class="row">
                                                    <div class="col-xs-4">
                                                        <div class="method-card online-method-card selected" data-channel="paynow_link">
                                                            <i class="fa fa-envelope-o text-primary"></i>
                                                            <span>Email Link</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-4">
                                                        <div class="method-card online-method-card" data-channel="ecocash_push">
                                                            <i class="fa fa-mobile"></i>
                                                            <span>EcoCash USSD Push</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-4">
                                                        <div class="method-card online-method-card" data-channel="card">
                                                            <i class="fa fa-cc-visa"></i>
                                                            <span>Card (Remote)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Parent/Payer Mobile Number</label>
                                                        <input type="text" name="payer_phone" id="payer_phone" class="form-control" placeholder="e.g. 077XXXXXXX" style="border-radius: 4px;">
                                                        <p class="help-block small text-muted">Required if pushing an EcoCash prompt directly to their phone.</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Parent/Payer Email</label>
                                                        <input type="email" name="payer_email" id="payer_email" class="form-control" placeholder="parent@example.com" style="border-radius: 4px;">
                                                        <p class="help-block small text-muted">The secure payment link will be sent here.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="box-footer" style="border-radius: 0 0 8px 8px;">
                                            <button type="submit" class="btn btn-primary btn-flat pull-right pay-online-btn" style="border-radius: 4px; padding: 8px 20px;">
                                                <i class="fa fa-paper-plane"></i> Send Remote Payment Link
                                            </button>
                                        </div>
                                    </form>
                                </div>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select2
            $('.select2, .select2-online').select2({
                placeholder: "Type student name or ID...",
                allowClear: true
            });

            // Datepicker
            $('#payment_date').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Tab 1: Direct Payment Method Selection
            $('.direct-method-card').on('click', function() {
                $('.direct-method-card').removeClass('selected');
                $(this).addClass('selected');
                $('#direct_payment_method').val($(this).data('method'));
            });

            // Tab 2: Remote Online Method Selection
            $('.online-method-card').on('click', function() {
                $('.online-method-card').removeClass('selected');
                $(this).addClass('selected');
                $('#online_channel').val($(this).data('channel'));

                if ($(this).data('channel') === 'ecocash_push') {
                    $('#payer_phone').prop('required', true);
                } else {
                    $('#payer_phone').prop('required', false);
                }
            });

            // Auto-fill parent contacts from student selection
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