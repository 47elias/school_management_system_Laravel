<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bulk Promotion | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-indigo: #4f46e5;
            --brand-indigo-hover: #4338ca;
            --brand-danger: #ef4444;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; }
        .content-wrapper { background-color: var(--bg-light) !important; }

        .promo-card {
            background: #ffffff; 
            border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .promo-card .box-header {
            padding: 24px 25px;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
        }

        .form-control-modern {
            border-radius: 8px; 
            border: 1px solid var(--border-color);
            height: 42px; 
            box-shadow: none; 
            transition: all 0.2s ease;
            font-size: 14px;
            color: #1e293b;
        }

        .form-control-modern:focus {
            border-color: var(--brand-indigo);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .table-promo thead th {
            background: #f1f5f9; 
            color: #475569;
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 1px;
            padding: 16px 20px; 
            border: none !important;
        }

        .table-promo tbody td {
            vertical-align: middle !important;
            padding: 18px 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
        }

        .table-promo tbody tr:hover {
            background-color: #f8fafc;
        }

        .btn-execute {
            background: var(--brand-indigo); 
            color: white;
            border: none; 
            border-radius: 10px; 
            padding: 12px 28px;
            font-weight: 700; 
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .btn-execute:hover { 
            background: var(--brand-indigo-hover); 
            color: white; 
            transform: translateY(-1px); 
            box-shadow: 0 6px 12px -2px rgba(79, 70, 229, 0.3);
        }

        .arrow-icon { 
            color: #94a3b8; 
            font-size: 16px;
            background: #f1f5f9;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .grade-badge {
            background: #f1f5f9; 
            color: #334155; 
            font-size: 13px; 
            font-weight: 600;
            padding: 6px 14px; 
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        input[type="checkbox"] {
            width: 18px; 
            height: 18px; 
            accent-color: var(--brand-indigo); 
            cursor: pointer;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 30px 25px 15px 25px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 26px; margin: 0;">
                    Mass Academic Promotion
                    <small style="display: block; color: #64748b; font-weight: 500; font-size: 13px; margin-top: 5px;">Year-End Transition Management</small>
                </h1>
                <ol class="breadcrumb" style="top: 25px;">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Bulk Promotion</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">

                        {{-- Alert Messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" style="border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check-circle"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" style="border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(239,68,68,0.15);">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="callout callout-info" style="border-radius: 12px; border-left-width: 5px; background: #eff6ff; color: #1e40af; border-color: #3b82f6;">
                            <h4><i class="fa fa-info-circle"></i> Batch Promotion Logic</h4>
                            <p style="margin-bottom: 0;">Map each current class precisely to its next destination. Active students will be cleanly migrated in a single batch pass. Use "Graduated" for final-year students.</p>
                        </div>

                        <form id="massPromotionForm" action="{{ route('students.promote.mass') }}" method="POST">
                            @csrf

                            {{-- Global Settings Card --}}
                            <div class="promo-card">
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-weight: 700; color: #1e293b; font-size: 16px;"><i class="fa fa-calendar text-indigo" style="margin-right: 8px;"></i> 1. Target Academic Term</h3>
                                </div>
                                <div class="box-body" style="padding: 25px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">Select Term Students Are Moving Into:</label>
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
                                    <h3 class="box-title" style="font-weight: 700; color: #1e293b; font-size: 16px;"><i class="fa fa-exchange text-indigo" style="margin-right: 8px;"></i> 2. Class Mapping Overview</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-promo">
                                        <thead>
                                            <tr>
                                                <th width="8%" class="text-center">Status</th>
                                                <th width="35%">Current Class (From)</th>
                                                <th width="12%" class="text-center">Transition</th>
                                                <th width="45%">Destination Class (To)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classes as $class)
                                            <tr>
                                                <td class="text-center">
                                                    {{-- Uses unique class ID as key to prevent index mismatch --}}
                                                    <input type="checkbox" name="promote[{{ $class->id }}][active]" value="1" checked>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="promote[{{ $class->id }}][from_grade]" value="{{ $class->class_name }}">
                                                    <span class="grade-badge">{{ $class->class_name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <i class="fa fa-long-arrow-right arrow-icon"></i>
                                                </td>
                                                <td>
                                                    <select name="promote[{{ $class->id }}][to_grade]" class="form-control form-control-modern">
                                                        <option value="">-- Skip / No Change --</option>
                                                        @foreach($classes as $destClass)
                                                            <option value="{{ $destClass->class_name }}">{{ $destClass->class_name }}</option>
                                                        @endforeach
                                                        <option value="Graduated" style="color: var(--brand-danger); font-weight: bold;">🎓 Graduated / Alumni</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="box-footer" style="padding: 25px; background: #f8fafc; border-top: 1px solid var(--border-color);">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted small" style="margin-top: 8px; margin-bottom: 0;">
                                                <i class="fa fa-warning text-yellow" style="margin-right: 4px;"></i>
                                                Verify that all financial accounts and fee mappings are updated for the target term before executing.
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="submit" class="btn btn-execute">
                                                <i class="fa fa-rocket" style="margin-right: 6px;"></i> PROCESS ALL PROMOTIONS
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
                    // Only validate if checked AND a destination was partially touched, or ensure selections make sense
                });

                return confirm("CRITICAL ACTION: You are about to process batch student promotions across your system. Proceed?");
            });
        });
    </script>
</body>
</html>