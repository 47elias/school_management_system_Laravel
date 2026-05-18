<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bulk Promotion | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-warning: #f59e0b;
            --brand-indigo: #4f46e5;
            --brand-danger: #ef4444;
            --bg-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; }
        .content-wrapper { background-color: var(--bg-light) !important; }

        .promo-card {
            background: #fff; border-radius: 16px; border: none;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }

        .form-control-modern {
            border-radius: 8px; border: 1px solid #e2e8f0;
            height: 38px; box-shadow: none; transition: all 0.2s;
        }

        .form-control-modern:focus {
            border-color: var(--brand-indigo);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .table-promo thead th {
            background: #f1f5f9; color: #475569;
            text-transform: uppercase; font-size: 11px; letter-spacing: 1px;
            padding: 15px; border: none !important;
        }

        .btn-execute {
            background: var(--brand-indigo); color: white;
            border: none; border-radius: 10px; padding: 12px 25px;
            font-weight: 700; transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }

        .btn-execute:hover { background: #4338ca; color: white; transform: translateY(-1px); }

        .arrow-icon { color: #cbd5e1; margin: 0 10px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 35px 25px;">
                <h1>
                    Mass Academic Promotion
                    <small>Year-End Transition Management</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Bulk Promotion</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">

                        {{-- Alert Messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" style="border-radius: 12px;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="callout callout-info" style="border-radius: 12px; border-left-width: 5px;">
                            <h4><i class="fa fa-info-circle"></i> Batch Promotion Logic</h4>
                            <p>Map each current class to its next destination. This will transition all <b>Active</b> students and initialize their ledger for the new term. Use "Graduated" for final-year students.</p>
                        </div>

                        <form id="massPromotionForm" action="{{ route('students.promote.mass') }}" method="POST">
                            @csrf

                            {{-- Global Settings Card --}}
                            <div class="promo-card">
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-weight: 700;">1. Target Academic Term</h3>
                                </div>
                                <div class="box-body" style="padding: 25px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="text-muted">SELECT THE TERM STUDENTS ARE MOVING INTO:</label>
                                            <select name="target_term_id" class="form-control form-control-modern" required>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                                        {{ $term->term_name }} ({{ $term->academic_year }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Transition Mapping Card --}}
                            <div class="promo-card">
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-weight: 700;">2. Class Mapping Overview</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-promo">
                                        <thead>
                                            <tr>
                                                <th width="10%">Status</th>
                                                <th width="35%">Current Class (From)</th>
                                                <th width="10%" class="text-center">Transition</th>
                                                <th width="45%">Destination Class (To)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classes as $index => $class)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="promote[{{$index}}][active]" value="1" checked>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="promote[{{$index}}][from_grade]" value="{{ $class->class_name }}">
                                                    <span class="label label-default" style="font-size: 13px; padding: 5px 10px;">{{ $class->class_name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <i class="fa fa-long-arrow-right arrow-icon"></i>
                                                </td>
                                                <td>
                                                    <select name="promote[{{$index}}][to_grade]" class="form-control form-control-modern" required>
                                                        <option value="">-- Select Destination --</option>
                                                        @foreach($classes as $destClass)
                                                            <option value="{{ $destClass->class_name }}">{{ $destClass->class_name }}</option>
                                                        @endforeach
                                                        <option value="Graduated" style="color: var(--brand-danger); font-weight: bold;">Graduated / Alumni</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="box-footer" style="padding: 25px; background: #fafafa;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted small">
                                                <i class="fa fa-warning text-yellow"></i>
                                                Ensure all financial records for the current term are finalized before executing.
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="submit" class="btn btn-execute">
                                                <i class="fa fa-rocket"></i> &nbsp;PROCESS ALL PROMOTIONS
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>

    @include('components.scripts')

    <script>
        $(document).ready(function() {
            $('#massPromotionForm').on('submit', function(e) {
                const selections = $(this).find('select[name$="[to_grade]"]');
                let valid = true;

                selections.each(function() {
                    const row = $(this).closest('tr');
                    const isChecked = row.find('input[type="checkbox"]').is(':checked');
                    if (isChecked && $(this).val() === "") {
                        alert("Please select a destination for all active rows.");
                        valid = false;
                        return false;
                    }
                });

                if(!valid) return false;

                return confirm("CRITICAL ACTION: You are about to promote all selected classes to the next academic year. This will generate new invoices and update student statuses. Proceed?");
            });
        });
    </script>
</body>
</html>

