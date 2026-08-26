<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CA Analytics | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.adminlte')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-orange: linear-gradient(45deg, #f39c12, #ffcc33);
            --grad-red: linear-gradient(45deg, #dd4b39, #ed5565);
            --grad-purple: linear-gradient(45deg, #605ca8, #9b8cf2);
        }
        body { background-color: #f0f3f7 !important; font-family: 'Source Sans Pro', sans-serif; }
        .small-box { border-radius: 15px; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; }
        .small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .small-box .inner { padding: 20px; }
        .small-box .icon { top: 5px; right: 15px; font-size: 60px; color: rgba(255,255,255,0.15); transition: 0.3s; }
        .small-box:hover .icon { color: rgba(255,255,255,0.3); transform: scale(1.1); }
        .bg-blue-grad { background: var(--grad-blue) !important; color: #fff; }
        .bg-green-grad { background: var(--grad-green) !important; color: #fff; }
        .bg-orange-grad { background: var(--grad-orange) !important; color: #fff; }
        .bg-red-grad { background: var(--grad-red) !important; color: #fff; }
        .bg-purple-grad { background: var(--grad-purple) !important; color: #fff; }
        .box { border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .box-header { padding: 15px; border-bottom: 1px solid #f4f4f4; }
        .box-header .box-title { font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-vibrant thead th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 11px; padding: 12px 10px; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .chart-container-sm { position: relative; height: 220px; width: 100%; }
        .badge-modern { padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 10px; }

        .rank-card { border-radius: 12px; padding: 20px; color: #fff; }
        .rank-card h4 { margin: 0 0 5px 0; font-weight: 300; opacity: .9; }
        .rank-card h2 { margin: 0; font-weight: 700; }
        .rank-card .sub { opacity: .85; font-size: 13px; margin-top: 5px; }

        #aiInsightsBox {
            background: #f8fafc; border-radius: 10px; padding: 18px; line-height: 1.7;
            font-size: 14px; color: #334155; white-space: pre-line; min-height: 60px;
        }
        .ai-loading { text-align: center; padding: 30px; color: #94a3b8; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    CA Analytics
                    <small>Statistical breakdown of Continuous Assessment across the school</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('activities.index') }}">Continuous Assessment</a></li>
                    <li class="active">Analytics</li>
                </ol>
            </section>

            <section class="content">

                {{-- TERM FILTER --}}
                <div class="box box-solid box-default">
                    <div class="box-body">
                        <form method="GET" class="form-inline">
                            <div class="form-group">
                                <label style="margin-right:8px;">Term</label>
                                <select name="term_id" class="form-control" onchange="this.form.submit()">
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                                            {{ $t->term_name }} ({{ $t->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KPI ROW --}}
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue-grad">
                            <div class="inner">
                                <h3>{{ $overall['avg_pct'] }}%</h3>
                                <p>School-Wide CA Average</p>
                            </div>
                            <div class="icon"><i class="fa fa-line-chart"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green-grad">
                            <div class="inner">
                                <h3>{{ $overall['activity_count'] }}</h3>
                                <p>Activities Logged</p>
                            </div>
                            <div class="icon"><i class="fa fa-tasks"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple-grad">
                            <div class="inner">
                                <h3>{{ $overall['marks_count'] }}</h3>
                                <p>Scores Recorded</p>
                            </div>
                            <div class="icon"><i class="fa fa-pencil"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red-grad">
                            <div class="inner">
                                <h3>{{ $overall['at_risk_count'] }}</h3>
                                <p>At-Risk Scores (&lt; 40%)</p>
                            </div>
                            <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>

                {{-- BEST / WORST CLASS CALLOUTS --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="rank-card" style="background: var(--grad-green);">
                            <h4><i class="fa fa-trophy"></i> Best Performing Class</h4>
                            @if($bestClass)
                                <h2>{{ $bestClass['class_name'] }} — {{ $bestClass['avg_pct'] }}%</h2>
                                <div class="sub">{{ $bestClass['marks_count'] }} scores recorded this term</div>
                            @else
                                <h2>No data yet</h2>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rank-card" style="background: var(--grad-red);">
                            <h4><i class="fa fa-arrow-down"></i> Lowest Performing Class</h4>
                            @if($worstClass)
                                <h2>{{ $worstClass['class_name'] }} — {{ $worstClass['avg_pct'] }}%</h2>
                                <div class="sub">{{ $worstClass['marks_count'] }} scores recorded this term</div>
                            @else
                                <h2>Not enough classes to compare</h2>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- AI INSIGHTS PANEL --}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-magic text-purple"></i> AI Analysis</h3>
                        <div class="box-tools">
                            <button id="generateInsightsBtn" class="btn btn-sm btn-primary">
                                <i class="fa fa-refresh"></i> Generate AI Insights
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div id="aiInsightsBox">
                            <span class="text-muted"><i class="fa fa-info-circle"></i> Click "Generate AI Insights" to get a written analysis of the trends, risks, and recommendations behind the numbers below.</span>
                        </div>
                    </div>
                </div>

                {{-- CLASS RANKING + SUBJECT BREAKDOWN --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-bar-chart text-blue"></i> CA Average by Class</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="classChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-bar-chart text-orange"></i> CA Average by Subject</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="subjectChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TREND + DISTRIBUTION --}}
                <div class="row">
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-line-chart text-green"></i> Weekly CA Average Trend</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-pie-chart text-purple"></i> Grade Distribution</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="distributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTIVITY TYPE + TOP/BOTTOM STUDENTS --}}
                <div class="row">
                    <div class="col-md-5">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-tags text-aqua"></i> Average by Activity Type</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container-sm">
                                    <canvas id="typeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-star text-yellow"></i> Top & Bottom Students (min. 3 scores)</h3>
                            </div>
                            <div class="box-body no-padding">
                                <div class="row" style="margin:0;">
                                    <div class="col-xs-6" style="padding:0;">
                                        <table class="table table-vibrant">
                                            <thead><tr><th colspan="2" class="text-green"><i class="fa fa-arrow-up"></i> Top 5</th></tr></thead>
                                            <tbody>
                                                @forelse($topStudents as $s)
                                                    <tr>
                                                        <td>{{ $s['student_name'] }}<br><small class="text-muted">{{ $s['class_name'] }}</small></td>
                                                        <td class="text-right text-bold text-green">{{ $s['avg_pct'] }}%</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="2" class="text-muted text-center">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-xs-6" style="padding:0; border-left:1px solid #f4f4f4;">
                                        <table class="table table-vibrant">
                                            <thead><tr><th colspan="2" class="text-red"><i class="fa fa-arrow-down"></i> Bottom 5</th></tr></thead>
                                            <tbody>
                                                @forelse($bottomStudents as $s)
                                                    <tr>
                                                        <td>{{ $s['student_name'] }}<br><small class="text-muted">{{ $s['class_name'] }}</small></td>
                                                        <td class="text-right text-bold text-red">{{ $s['avg_pct'] }}%</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="2" class="text-muted text-center">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
        @include('layouts.footer')
    </div>

    @include('components.scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const classLabels = {!! json_encode($byClass->pluck('class_name')) !!};
            const classData = {!! json_encode($byClass->pluck('avg_pct')) !!};
            const subjectLabels = {!! json_encode($bySubject->pluck('subject_name')) !!};
            const subjectData = {!! json_encode($bySubject->pluck('avg_pct')) !!};
            const trendLabels = {!! json_encode($trend->pluck('week_start')) !!};
            const trendData = {!! json_encode($trend->pluck('avg_pct')) !!};
            const typeLabels = {!! json_encode($byType->pluck('type')) !!};
            const typeData = {!! json_encode($byType->pluck('avg_pct')) !!};
            const distLabels = {!! json_encode(array_keys($distribution)) !!};
            const distData = {!! json_encode(array_values($distribution)) !!};

            const tooltipDefaults = {
                backgroundColor: '#1e293b', padding: 12,
                titleFont: { size: 14 }, bodyFont: { size: 13 }, cornerRadius: 8
            };

            function barColor(values) {
                // Highlight best (green) and worst (red) bar, blue-gradient the rest.
                if (!values.length) return ['#0073b7'];
                const max = Math.max(...values), min = Math.min(...values);
                return values.map(v => {
                    if (v === max && max !== min) return '#00a65a';
                    if (v === min && max !== min) return '#dd4b39';
                    return '#00c0ef';
                });
            }

            // --- Class ranking chart ---
            new Chart(document.getElementById('classChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: classLabels.length ? classLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: classData.length ? classData : [0],
                        backgroundColor: barColor(classData),
                        borderRadius: 8, borderSkipped: false, barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // --- Subject breakdown chart ---
            new Chart(document.getElementById('subjectChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: subjectLabels.length ? subjectLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: subjectData.length ? subjectData : [0],
                        backgroundColor: barColor(subjectData),
                        borderRadius: 8, borderSkipped: false, barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // --- Weekly trend line chart ---
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            const trendGrad = trendCtx.createLinearGradient(0, 0, 0, 300);
            trendGrad.addColorStop(0, 'rgba(0,166,90,0.35)');
            trendGrad.addColorStop(1, 'rgba(0,166,90,0)');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels.length ? trendLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: trendData.length ? trendData : [0],
                        borderColor: '#00a65a', backgroundColor: trendGrad,
                        fill: true, tension: 0.35, pointRadius: 4,
                        pointBackgroundColor: '#00a65a'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // --- Grade distribution doughnut ---
            new Chart(document.getElementById('distributionChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distData,
                        backgroundColor: ['#00a65a', '#00c0ef', '#0073b7', '#f39c12', '#ff8f00', '#dd4b39']
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }, tooltip: tooltipDefaults }
                }
            });

            // --- Activity type horizontal bar ---
            new Chart(document.getElementById('typeChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: typeLabels.length ? typeLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: typeData.length ? typeData : [0],
                        backgroundColor: '#605ca8',
                        borderRadius: 6, borderSkipped: false, barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // --- AI Insights (on-demand) ---
            const btn = document.getElementById('generateInsightsBtn');
            const box = document.getElementById('aiInsightsBox');
            const termId = {{ $selectedTerm->id ?? 'null' }};

            btn.addEventListener('click', function () {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Analyzing...';
                box.innerHTML = '<div class="ai-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Crunching the numbers and writing the analysis...</div>';

                fetch("{{ route('activities.analytics.ai_insights') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ term_id: termId })
                })
                .then(r => r.json())
                .then(data => {
                    box.innerText = data.insights;
                })
                .catch(() => {
                    box.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle"></i> Something went wrong generating the analysis. Please try again.</span>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-refresh"></i> Regenerate';
                });
            });
        });
    </script>
</body>
</html>
