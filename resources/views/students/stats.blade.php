<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Enrollment Analytics | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #4f46e5;
            --brand-success: #10b981;
            --brand-info: #0ea5e9;
            --brand-warning: #f59e0b;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
        }

        body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; }

        /* Fix AdminLTE background conflicts */
        .content-wrapper { background-color: var(--bg-light) !important; }

        /* Modern Dashboard Cards */
        .stats-card {
            background: #fff; border-radius: 16px; padding: 20px;
            display: flex; align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 25px; border: 1px solid #f1f5f9;
            transition: transform 0.2s ease;
        }
        .stats-card:hover { transform: translateY(-3px); }

        .stats-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-right: 15px;
        }
        .stats-data .number { font-size: 26px; font-weight: 800; color: var(--text-main); display: block; line-height: 1; }
        .stats-data .label { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Container Styling */
        .box-modern {
            background: #fff; border-radius: 16px; border: none;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        .box-modern .box-header { padding: 20px 25px; background: transparent; border-bottom: 1px solid #f1f5f9; }
        .box-modern .box-title { font-weight: 700; color: var(--text-main); font-size: 17px; }

        /* Table Aesthetics */
        .table-modern thead th {
            background: #f8fafc; text-transform: uppercase; font-size: 11px;
            letter-spacing: 1px; color: #64748b; padding: 12px 25px; border: none;
        }
        .table-modern tbody td { padding: 15px 25px; vertical-align: middle; color: #334155; border-top: 1px solid #f1f5f9; }

        .progress-slim { height: 8px; border-radius: 10px; background: #f1f5f9; margin-top: 5px; overflow: hidden; }
        .progress-bar-indigo { background: linear-gradient(90deg, #4f46e5, #6366f1); }
        .bg-purple { background-color: #a855f7 !important; }
        .bg-blue { background-color: #3b82f6 !important; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        {{-- 1. TOP NAVIGATION --}}
        @include('layouts.topbar')

        {{-- 2. LEFT SIDEBAR --}}
        @include('layouts.sidebar')

        {{-- 3. MAIN CONTENT --}}
        <div class="content-wrapper">
            <section class="content-header" style="padding: 35px 25px 20px;">
                <h1 style="font-weight: 800; color: #0f172a; letter-spacing: -1px;">
                    Enrollment Dashboard
                    <small style="font-weight: 500; color: #64748b; margin-left: 10px;">Academic Analytics Overview</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Enrollment Analytics</li>
                </ol>
            </section>

            <section class="content">
                {{-- Quick Stats Row --}}
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--brand-primary);">
                                <i class="fa fa-graduation-cap"></i>
                            </div>
                            <div class="stats-data">
                                <span class="number">{{ $classStats->sum('total') }}</span>
                                <span class="label">Total Students</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                                <i class="fa fa-flag-checkered"></i>
                            </div>
                            <div class="stats-data">
                                <span class="number">{{ \App\Models\Term::where('is_current', true)->value('term_name') ?? 'None' }}</span>
                                <span class="label">Active Term</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(14, 165, 233, 0.1); color: var(--brand-info);">
                                <i class="fa fa-building"></i>
                            </div>
                            <div class="stats-data">
                                <span class="number">{{ $classStats->count() }}</span>
                                <span class="label">Class Groups</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                                <i class="fa fa-venus-mars"></i>
                            </div>
                            <div class="stats-data">
                                @php
                                    $f = $genderStats->where('gender', 'Female')->first()->total ?? 0;
                                    $m = $genderStats->where('gender', 'Male')->first()->total ?? 0;
                                @endphp
                                <span class="number">{{ $f }} / {{ $m }}</span>
                                <span class="label">Girls vs Boys</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Main Table --}}
                    <div class="col-md-8">
                        <div class="box box-modern">
                            <div class="box-header">
                                <h3 class="box-title">Enrollment by Grade</h3>
                            </div>
                            <div class="box-body no-padding">
                                <div class="table-responsive">
                                    <table class="table table-modern">
                                        <thead>
                                            <tr>
                                                <th>Grade Level</th>
                                                <th>Distribution Visual</th>
                                                <th class="text-right">Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $maxVal = $classStats->max('total') ?: 1; @endphp
                                            @foreach($classStats as $stat)
                                            <tr>
                                                <td style="font-weight: 700;">{{ $stat->grade }}</td>
                                                <td width="55%">
                                                    <div class="progress-slim">
                                                        <div class="progress-bar progress-bar-indigo"
                                                             style="width: {{ ($stat->total / $maxVal) * 100 }}%"></div>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <span class="badge" style="background: #eef2ff; color: #4338ca; padding: 5px 10px;">{{ $stat->total }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Side Info --}}
                    <div class="col-md-4">
                        <div class="box box-modern">
                            <div class="box-header"><h3 class="box-title">Term Growth</h3></div>
                            <div class="box-body no-padding">
                                <table class="table table-modern">
                                    <tbody>
                                        @foreach($termStats as $t)
                                        <tr>
                                            <td>
                                                <span style="display:block; font-weight: 700;">{{ $t->term_name }}</span>
                                                <small class="text-muted">{{ $t->academic_year }}</small>
                                            </td>
                                            <td class="text-right text-success" style="font-weight: 800;">+{{ $t->total }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="box box-modern">
                            <div class="box-header"><h3 class="box-title">Gender Balance</h3></div>
                            <div class="box-body" style="padding: 20px 25px;">
                                @php $total = $classStats->sum('total') ?: 1; @endphp
                                @foreach($genderStats as $g)
                                <div style="margin-bottom: 20px;">
                                    <div class="clearfix" style="margin-bottom: 5px;">
                                        <span class="pull-left" style="font-weight: 600;">{{ $g->gender }}</span>
                                        <span class="pull-right text-muted">{{ $g->total }} Students</span>
                                    </div>
                                    <div class="progress-slim">
                                        <div class="progress-bar {{ $g->gender == 'Female' ? 'bg-purple' : 'bg-blue' }}"
                                             style="width: {{ ($g->total / $total) * 100 }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- 4. FOOTER --}}
        @include('layouts.footer')

    </div>

    {{-- 5. SCRIPTS --}}
    @include('components.scripts')
</body>
</html>
