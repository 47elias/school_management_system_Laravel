<!DOCTYPE html>
<html>
<head>
    <title>Collect Fees | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    {{-- Add Datepicker CSS if not in adminlte component --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Fee Collection <small>Record New Payment</small></h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-money"></i> Payment Details</h3>
                            </div>

                            <form action="{{ route('fees.store') }}" method="POST">
                                @csrf
                                <div class="box-body">
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
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Type student name or ID...",
                allowClear: true
            });

            // Initialize Datepicker with DD/MM/YYYY format
            $('#payment_date').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
</body>
</html>
