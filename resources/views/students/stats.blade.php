<!DOCTYPE html>
<html lang="en">
<head>
    <title>Enrollment Analytics | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Scoped Modern UI Updates (Will not affect AdminLTE Footer/Layout) -->
    <style>
        /* Modern Gradient Cards */
        .content .info-box-modern { border-radius: 12px; padding: 25px 20px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); transition: transform 0.3s ease; margin-bottom: 20px; }
        .content .info-box-modern:hover { transform: translateY(-5px); }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .content .info-box-modern .inner h3 { font-size: 36px; font-weight: 800; margin: 0 0 5px 0; letter-spacing: 1px; }
        .content .info-box-modern .inner p { font-size: 15px; margin: 0; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
        .content .info-box-modern .icon { position: absolute; right: 20px; top: 20px; font-size: 55px; opacity: 0.2; }

        /* Modern Box Styling */
        .content .box { border-radius: 12px; border-top: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; background: #ffffff; }
        .content .box-header { border-bottom: 1px solid #f1f5f9; padding: 20px; background: #fff; }
        .content .box-title { font-weight: 800 !important; color: #1e293b; font-size: 18px; }
        
        /* Table overrides */
        .content .table > tbody > tr > td { vertical-align: middle !important; padding: 16px 20px; border-top: 1px solid #f1f5f9; font-size: 15px; color: #334155; }
        .content .table > thead > tr > th { border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; padding: 16px 20px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; background: #f8fafc; }
        .content .table-hover > tbody > tr:hover { background-color: #f8fafc; }

        /* Analytics Specific Styles */
        .content .progress-slim { height: 8px; border-radius: 10px; background: #f1f5f9; margin-top: 5px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
        .content .progress-bar-indigo { background: linear-gradient(90deg, #4f46e5, #6366f1); }
        .content .bg-purple { background-color: #a855f7 !important; }
        .content .bg-blue { background-color: #3b82f6 !important; }
        .content .badge-count { background: #eef2ff; color: #4338ca; padding: 6px 12px; font-weight: 700; font-size: 13px; border-radius: 6px; }
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
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Enrollment Dashboard
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Academic Analytics Overview</small>
                </h1>
            </section>

            <section class="content">
                {{-- Quick Stats Row --}}
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-blue">
                            <div class="inner">
                                <h3>{{ $classStats->sum('total') }}</h3>
                                <p>Total Students</p>
                            </div>
                            <div class="icon"><i class="fa fa-graduation-cap"></i></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-green">
                            <div class="inner">
                                <h3>{{ \App\Models\Term::where('is_current', true)->value('term_name') ?? 'None' }}</h3>
                                <p>Active Term</p>
                            </div>
                            <div class="icon"><i class="fa fa-flag-checkered"></i></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-info">
                            <div class="inner">
                                <h3>{{ $classStats->count() }}</h3>
                                <p>Class Groups</p>
                            </div>
                            <div class="icon"><i class="fa fa-building"></i></div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-warning">
                            <div class="inner">
                                @php
                                    $f = $genderStats->where('gender', 'Female')->first()->total ?? 0;
                                    $m = $genderStats->where('gender', 'Male')->first()->total ?? 0;
                                @endphp
                                <h3>{{ $f }} / {{ $m }}</h3>
                                <p>Girls vs Boys</p>
                            </div>
                            <div class="icon"><i class="fa fa-venus-mars"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Main Table --}}
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-bar-chart text-blue"></i> Enrollment by Grade</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
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
                                            <td style="font-weight: 700; color: #1e293b;">{{ $stat->grade }}</td>
                                            <td width="55%">
                                                <div class="progress-slim">
                                                    <div class="progress-bar progress-bar-indigo"
                                                         style="width: {{ ($stat->total / $maxVal) * 100 }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <span class="badge-count">{{ $stat->total }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Side Info --}}
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-line-chart text-green"></i> Term Growth</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <tbody>
                                        @foreach($termStats as $t)
                                        <tr>
                                            <td>
                                                <span style="display:block; font-weight: 700; color: #1e293b;">{{ $t->term_name }}</span>
                                                <span class="label" style="background: #e2e8f0; color: #475569; font-size: 11px; padding: 3px 6px;">{{ $t->academic_year }}</span>
                                            </td>
                                            <td class="text-right text-success" style="font-weight: 800; font-size: 16px;">
                                                <i class="fa fa-arrow-up"></i> {{ $t->total }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-pie-chart text-orange"></i> Gender Balance</h3>
                            </div>
                            <div class="box-body" style="padding: 20px 25px;">
                                @php $total = $classStats->sum('total') ?: 1; @endphp
                                @foreach($genderStats as $g)
                                <div style="margin-bottom: 20px;">
                                    <div class="clearfix" style="margin-bottom: 5px;">
                                        <span class="pull-left" style="font-weight: 700; color: #1e293b;">
                                            <i class="fa {{ $g->gender == 'Female' ? 'fa-female text-purple' : 'fa-male text-blue' }}" style="margin-right: 5px;"></i> {{ $g->gender }}
                                        </span>
                                        <span class="pull-right text-muted" style="font-weight: 600;">{{ $g->total }} Students</span>
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