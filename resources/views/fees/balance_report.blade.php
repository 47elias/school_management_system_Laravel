<!DOCTYPE html>
<html>
<head>
    <title>Balance Report | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <style>
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-orange: linear-gradient(45deg, #f39c12, #ffcc33);
            --grad-red: linear-gradient(45deg, #dd4b39, #ed5565);
            --grad-dark: linear-gradient(45deg, #2c3e50, #34495e);
            --grad-gray: linear-gradient(45deg, #6c757d, #adb5bd);
        }

        body { background-color: #f0f3f7 !important; font-family: 'Source Sans Pro', sans-serif; }

        .info-box-text { text-transform: uppercase; font-weight: 600; font-size: 11px; }
        .info-box-number { font-size: 18px; }
        .text-bold { font-weight: bold; }
        .text-red { color: #dd4b39 !important; }
        .text-green { color: #00a65a !important; }
        .badge-new { background-color: #007bff !important; color: white; padding: 3px 7px; border-radius: 4px; font-size: 10px; }

        /* Modern Box Styling */
        .box, .info-box, .small-box { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .small-box:hover, .info-box:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        .box-header { padding: 15px; border-bottom: 1px solid #f4f4f4; }
        .box-header .box-title { font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Gradients mapping to AdminLTE classes */
        .bg-navy { background: var(--grad-dark) !important; color: #fff; }
        .bg-green { background: var(--grad-green) !important; color: #fff; }
        .bg-red { background: var(--grad-red) !important; color: #fff; }
        .bg-aqua { background: var(--grad-blue) !important; color: #fff; }
        .bg-gray { background: var(--grad-gray) !important; color: #fff !important; }

        .table-vibrant thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        /* Print Styles */
        @media print {
            .no-print, .main-header, .main-sidebar, .main-footer, .filter-box { display: none !important; }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: white !important; }
            .box { border: 1px solid #eee !important; box-shadow: none !important; }
            .print-header { display: block !important; }
            .table-bordered > thead > tr > th { background-color: #f4f4f4 !important; color: #000 !important; border: 1px solid #000 !important; }
            .table-bordered > tbody > tr > td { border: 1px solid #000 !important; }
        }
        .print-header { display: none; text-align: center; margin-bottom: 20px; border-bottom: 3px double #333; padding-bottom: 10px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="content-wrapper">
        {{-- Formal Print Header --}}
        <div class="print-header">
            <h2 style="margin:0;">{{ env('SCHOOL_NAME', 'Knowledge Planet College') }}</h2>
            <h4 style="text-transform: uppercase; letter-spacing: 2px;">Financial Balance Report</h4>
            <p><b>Target Term:</b> {{ $currentTerm->term_name }} | <b>Date:</b> {{ date('d/m/Y H:i') }}</p>
        </div>

        <section class="content-header no-print" style="padding: 25px 25px 10px 25px;">
            <h1>
                <span class="text-bold"><i class="fa fa-line-chart text-blue"></i> Financial Revenue & Balance</span>
                <div class="pull-right">
                    <button onclick="exportToExcel()" class="btn btn-success btn-sm text-bold" style="border-radius: 6px;">
                        <i class="fa fa-file-excel-o"></i> EXPORT EXCEL
                    </button>
                    <button onclick="window.print()" class="btn btn-default btn-sm text-bold" style="border-radius: 6px; margin-left: 5px;">
                        <i class="fa fa-print"></i> PRINT
                    </button>
                </div>
            </h1>
        </section>

        <section class="content">
            {{-- Top Info Boxes --}}
            <div class="row no-print">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-navy">
                        <span class="info-box-icon"><i class="fa fa-university"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">School Fund Balance</span>
                            <span class="info-box-number">${{ number_format($schoolBalance, 2) }}</span>
                            <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                            <span class="progress-description">Lifetime Net Cash</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Today's Income</span>
                            <span class="info-box-number">${{ number_format($dailyIncome, 2) }}</span>
                            <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                            <span class="progress-description">Payments Received Today</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Term Expenses</span>
                            <span class="info-box-number">${{ number_format($currentTermExpenses, 2) }}</span>
                            <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                            <span class="progress-description">Salaries & General Costs</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="box box-solid" style="height: 90px; display: flex; align-items: center; justify-content: center; background: transparent; box-shadow: none;">
                        <a href="{{ route('expenses.index') }}" class="btn btn-danger btn-block text-bold" style="margin: 10px; padding: 15px; border-radius: 8px; font-size: 14px;">
                            <i class="fa fa-minus-circle"></i> RECORD EXPENSE
                        </a>
                    </div>
                </div>
            </div>

            {{-- Filter Box --}}
            <div class="box box-default no-print filter-box" style="border-top: 3px solid #3c8dbc;">
                <div class="box-header with-border">
                    <h3 class="box-title text-bold"><i class="fa fa-filter text-blue"></i> Search & Term Filters</h3>
                </div>
                <form action="{{ route('fees.report') }}" method="GET">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Search Name/ID</label>
                                <input type="text" name="search" class="form-control" value="{{ $searchName }}" placeholder="Enter Student Name..." style="border-radius: 6px;">
                            </div>
                            <div class="col-md-2">
                                <label>Grade</label>
                                <select name="grade" class="form-control" style="border-radius: 6px;">
                                    <option value="">All Grades</option>
                                    @foreach($grades as $g)
                                        <option value="{{ $g }}" {{ $selectedGrade == $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Target View Session</label>
                                <select name="term_id" class="form-control" style="border-radius: 6px;">
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ ($selectedTermId ?? $currentTerm->id) == $term->id ? 'selected' : '' }}>
                                            {{ $term->term_name }} ({{ $term->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select name="status" class="form-control" style="border-radius: 6px;">
                                    <option value="">All Students</option>
                                    <option value="arrears" {{ $status == 'arrears' ? 'selected' : '' }}>Arrears Only</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block text-bold uppercase" style="border-radius: 6px;">Apply Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-gray">
                        <div class="inner"><h3>${{ number_format($report->sum('expected'), 2) }}</h3><p>Total Valid Billing</p></div>
                        <div class="icon"><i class="fa fa-calculator"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-green">
                        <div class="inner"><h3>${{ number_format($report->sum('paid'), 2) }}</h3><p>Actual Collected</p></div>
                        <div class="icon"><i class="fa fa-bank"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-red">
                        <div class="inner"><h3>${{ number_format($report->sum('balance'), 2) }}</h3><p>Total Arrears</p></div>
                        <div class="icon"><i class="fa fa-warning"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-aqua">
                        <div class="inner"><h3>${{ number_format($report->sum('monthly_arrears'), 2) }}</h3><p>Current Term Dues</p></div>
                        <div class="icon"><i class="fa fa-bullseye"></i></div>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="box box-primary">
                <div class="box-body no-padding table-responsive">
                    <table id="reportTable" class="table table-vibrant table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th class="text-center">Grade</th>
                                <th class="text-right">Billed (Since Join)</th>
                                <th class="text-right">Total Paid</th>
                                <th class="text-center">Term Status</th>
                                <th class="text-right">Current Balance</th>
                                <th class="no-print text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report as $data)
                            <tr>
                                <td>{{ $data->student_number }}</td>
                                <td>
                                    <span class="text-bold">{{ $data->name }} {{ $data->surname }}</span>
                                    @if($data->term_id == ($selectedTermId ?? $currentTerm->id))
                                        <span class="badge-new no-print">NEW ENROLL</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $data->grade }}</td>
                                <td class="text-right">${{ number_format($data->expected, 2) }}</td>
                                <td class="text-right text-green text-bold">${{ number_format($data->paid, 2) }}</td>
                                <td class="text-center">
                                    @if($data->monthly_arrears > 0)
                                        <span class="label label-danger" style="border-radius: 4px; padding: 4px 8px;">${{ number_format($data->monthly_arrears, 2) }} Owed</span>
                                    @else
                                        <span class="label label-success" style="border-radius: 4px; padding: 4px 8px;"><i class="fa fa-check"></i> CLEARED</span>
                                    @endif
                                </td>
                                <td class="text-right text-bold {{ $data->balance > 0 ? 'text-red' : 'text-green' }}">
                                    ${{ number_format($data->balance, 2) }}
                                </td>
                                <td class="no-print text-center">
                                    <a href="{{ route('fees.show', $data->id) }}" class="btn btn-xs btn-primary text-bold" style="border-radius: 4px;">
                                        VIEW STATEMENT
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc; font-weight: 700;">
                                <td colspan="3" class="text-right">GRAND TOTALS:</td>
                                <td class="text-right">${{ number_format($report->sum('expected'), 2) }}</td>
                                <td class="text-right text-green">${{ number_format($report->sum('paid'), 2) }}</td>
                                <td class="text-center text-red">${{ number_format($report->sum('monthly_arrears'), 2) }}</td>
                                <td class="text-right text-red">${{ number_format($report->sum('balance'), 2) }}</td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    function exportToExcel() {
        var table = document.getElementById("reportTable");
        var html = table.outerHTML;
        // Clean up icons and action buttons for Excel
        html = html.replace(/<i class=".*?"><\/i>/g, '');
        html = html.replace(/<th class="no-print.*?<\/th>/g, '');
        html = html.replace(/<td class="no-print.*?<\/td>/g, '');
        window.open('data:application/vnd.ms-excel,' + encodeURIComponent(html));
    }
</script>
</body>
</html>