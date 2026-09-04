<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bulk Promotion | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">

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
        @include('layouts.layout_separator')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 35px 25px;">
                <h1>Mass Academic Promotion <small>Year-End Transition Management</small></h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 12px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('success') }}
                    </div>
                @endif

                <form id="massPromotionForm" action="{{ route('students.promote.mass') }}" method="POST">
                    @csrf

                    <div class="promo-card">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="font-weight: 700;">1. Target Academic Term</h3>
                        </div>
                        <div class="box-body" style="padding: 25px;">
                            <select name="target_term_id" class="form-control form-control-modern" style="max-width: 300px;" required>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                        {{ $term->term_name }} ({{ $term->academic_year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="promo-card">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="font-weight: 700;">2. Class Mapping Overview</h3>
                        </div>
                        <div class="box-body no-padding">
                            <table id="promo-table" class="table table-promo">
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
                                            <input type="hidden" name="promote[{{$index}}][from_class_id]" value="{{ $class->id }}">
                                            <span class="label label-default" style="font-size: 13px; padding: 5px 10px;">{{ $class->class_name }}</span>
                                        </td>
                                        <td class="text-center"><i class="fa fa-long-arrow-right arrow-icon"></i></td>
                                        <td>
                                            <select name="promote[{{$index}}][to_class_id]" class="form-control form-control-modern" required>
                                                <option value="">-- Select Destination --</option>
                                                @foreach($classes as $destClass)
                                                    <option value="{{ $destClass->id }}">{{ $destClass->class_name }}</option>
                                                @endforeach
                                                <option value="graduated" class="text-danger" style="font-weight: bold;">Graduated / Alumni</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer" style="padding: 25px;">
                            <button type="submit" class="btn btn-execute pull-right">
                                <i class="fa fa-rocket"></i> PROCESS ALL PROMOTIONS
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#promo-table').DataTable({ "paging": false, "searching": true });
            $('#massPromotionForm').on('submit', function() {
                return confirm("Are you sure? This will update student statuses and class assignments across your system.");
            });
        });
    </script>
</body>
</html>
