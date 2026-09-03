<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dashboard | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    @include('components.scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-orange: linear-gradient(45deg, #f39c12, #ffcc33);
            --grad-red: linear-gradient(45deg, #dd4b39, #ed5565);
            --grad-dark: linear-gradient(45deg, #2c3e50, #34495e);
        }

        body { background-color: #f0f3f7 !important; font-family: 'Source Sans Pro', sans-serif; }

        /* Small-Box Modernization */
        .small-box {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
        }
        .small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .small-box .inner { padding: 20px; }
        .small-box .icon { top: 5px; right: 15px; font-size: 60px; color: rgba(255,255,255,0.15); transition: 0.3s; }
        .small-box:hover .icon { color: rgba(255,255,255,0.3); transform: scale(1.1); }

        /* Gradient Classes */
        .bg-blue-grad { background: var(--grad-blue) !important; color: #fff; }
        .bg-green-grad { background: var(--grad-green) !important; color: #fff; }
        .bg-orange-grad { background: var(--grad-orange) !important; color: #fff; }
        .bg-red-grad { background: var(--grad-red) !important; color: #fff; }
        .bg-dark-grad { background: var(--grad-dark) !important; color: #fff; }

        /* Box Styling */
        .box { border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .box-header { padding: 15px; border-bottom: 1px solid #f4f4f4; }
        .box-header .box-title { font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .table-vibrant thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 10px;
        }

        /* Chart Container Fix */
        .chart-container { position: relative; height: 320px; width: 100%; }

        .badge-modern { padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 10px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 25px 25px 10px 25px;">
                <h1>
                    <span class="text-bold">System Dashboard</span>
                    <small>{{ env('SCHOOL_NAME') }}</small>
                </h1>
            </section>

            <section class="content">
                {{-- Financial Stats Row --}}
                <h4 class="text-bold" style="margin-top: 10px; margin-bottom: 15px; color: #333; padding-left: 15px;">Financial Overview</h4>
                <div class="row">
                    <div class="col-lg-4 col-xs-12">
                        <div class="small-box bg-green-grad">
                            <div class="inner">
                                <h3>${{ number_format($totalRevenue, 2) }}</h3>
                                <p>Total Revenue (Payments)</p>
                            </div>
                            <div class="icon"><i class="fa fa-money"></i></div>
                            <a href="{{ route('fees.index') }}" class="small-box-footer">View Income <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <div class="small-box bg-red-grad">
                            <div class="inner">
                                <h3>${{ number_format($totalExpenses, 2) }}</h3>
                                <p>Total Expenses & Payroll</p>
                            </div>
                            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                            <a href="{{ route('expenses.index') }}" class="small-box-footer">View Outgoings <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <div class="small-box {{ $netBalance >= 0 ? 'bg-blue-grad' : 'bg-orange-grad' }}">
                            <div class="inner">
                                <h3>${{ number_format($netBalance, 2) }}</h3>
                                <p>Net Balance</p>
                            </div>
                            <div class="icon"><i class="fa fa-bank"></i></div>
                            <a href="{{ route('fees.report') }}" class="small-box-footer">Full Balance Report <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                {{-- General Stats Row --}}
                <h4 class="text-bold" style="margin-top: 10px; margin-bottom: 15px; color: #333; padding-left: 15px;">Academic & Operations</h4>
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue-grad">
                            <div class="inner">
                                <h3>{{ number_format($studentCount) }}</h3>
                                <p>Registered Students</p>
                            </div>
                            <div class="icon"><i class="fa fa-users"></i></div>
                            <a href="{{ route('students.index') }}" class="small-box-footer">View All <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green-grad">
                            <div class="inner">
                                <h3>{{ $subjectCount }}</h3>
                                <p>Active Subjects</p>
                            </div>
                            <div class="icon"><i class="fa fa-book"></i></div>
                            <a href="{{ route('subjects.index') }}" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-orange-grad">
                            <div class="inner">
                                <h3>{{ \App\Models\InventoryItem::whereColumn('quantity', '<=', 'alert_level')->count() }}</h3>
                                <p>Low Stock Items</p>
                            </div>
                            <div class="icon"><i class="fa fa-warning"></i></div>
                            <a href="{{ route('inventory.alerts') }}" class="small-box-footer">Stock Details <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-dark-grad">
                            <div class="inner">
                                <h3>{{ $userCount }}</h3>
                                <p>Staff Accounts</p>
                            </div>
                            <div class="icon"><i class="fa fa-lock"></i></div>
                            <a href="#" class="small-box-footer">Access Logs <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Chart Column --}}
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-bar-chart text-blue"></i> Enrollment by Grade</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="enrollmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Column --}}
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-fire text-orange"></i> Top Issued Supplies</h3>
                            </div>
                            <div class="box-body no-padding">
                                <ul class="nav nav-stacked">
                                    @forelse($topIssuedItems as $log)
                                    <li style="padding: 12px 15px; border-bottom: 1px solid #f9f9f9;">
                                        <span class="text-bold text-gray-dark">{{ $log->item->item_name }}</span>
                                        <span class="pull-right badge bg-orange">{{ $log->total_issued }}</span>
                                        <div class="text-muted small">Released this month</div>
                                    </li>
                                    @empty
                                    <li class="padding: 20px; text-center text-muted">No data available</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="box-footer text-center">
                                <a href="{{ route('inventory.index') }}" class="text-orange small text-bold uppercase">View Inventory Report</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Exam Table --}}
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-calendar text-info"></i> Upcoming Examinations</h3>
                            </div>
                            <div class="box-body no-padding">
                                <div class="table-responsive">
                                    <table class="table table-vibrant table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ref ID</th>
                                                <th>Exam Name</th>
                                                <th>Subject</th>
                                                <th>Date & Day</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentExams as $exam)
                                            <tr>
                                                <td><span class="label label-default">#{{ $exam->id }}</span></td>
                                                <td class="text-bold">{{ $exam->exam_name }}</td>
                                                <td>{{ $exam->subject->subject_name ?? 'N/A' }}</td>
                                                <td><i class="fa fa-clock-o text-muted"></i> {{ \Carbon\Carbon::parse($exam->exam_date)->format('D, d M Y') }}</td>
                                                <td class="text-center">
                                                    <span class="badge-modern bg-green-grad">CONFIRMED</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctxEnroll = document.getElementById('enrollmentChart').getContext('2d');

            // Create Gradient
            const chartGrad = ctxEnroll.createLinearGradient(0, 0, 0, 400);
            chartGrad.addColorStop(0, '#0073b7');
            chartGrad.addColorStop(1, '#00c0ef');

            const labels = {!! json_encode($gradesData->pluck('grade')) !!};
            const data = {!! json_encode($gradesData->pluck('total')) !!};

            new Chart(ctxEnroll, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        label: 'Students',
                        data: data.length ? data : [0],
                        backgroundColor: chartGrad,
                        hoverBackgroundColor: '#005a91',
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 11, weight: 'bold' } }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>