<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>My Subjects | SIT</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        /* Modernizing AdminLTE Cards */
        .widget-user-2 .widget-user-header {
            padding: 20px;
            border-top-right-radius: 3px;
            border-top-left-radius: 3px;
        }
        .subject-icon {
            width: 65px;
            height: 65px;
            background: rgba(255,255,255,0.2);
            border: 2px solid #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            float: left;
        }
        .subject-info-box {
            margin-left: 80px;
        }
        .widget-user-username {
            margin-top: 5px !important;
            margin-left: 0 !important;
            font-weight: 600 !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .widget-user-desc {
            margin-left: 0 !important;
            opacity: 0.9;
            font-size: 16px !important;
        }
        .nav-stacked > li {
            border-bottom: 1px solid #f4f4f4;
        }
        .nav-stacked > li:last-child {
            border-bottom: none;
        }
        .btn-manage {
            transition: all 0.3s;
        }
        .btn-manage:hover {
            padding-left: 10px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            {{-- PAGE HEADER --}}
            <section class="content-header">
                <h1>
                    My Teaching Load
                    <small>Academic Session {{ date('Y') }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">My Subjects</li>
                </ol>
            </section>

            {{-- MAIN CONTENT --}}
            <section class="content">
                <div class="row">
                    @php
                        // Array of colors to rotate through the cards
                        $colors = ['bg-blue', 'bg-purple', 'bg-maroon', 'bg-teal', 'bg-orange', 'bg-aqua'];
                    @endphp

                    @forelse($assignments as $index => $item)
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="box box-widget widget-user-2 shadow-sm">
                                {{-- Header with dynamic color rotation --}}
                                <div class="widget-user-header {{ $colors[$index % count($colors)] }}">
                                    <div class="subject-icon">
                                        {{ substr($item->subject->subject_name, 0, 1) }}
                                    </div>
                                    <div class="subject-info-box">
                                        <h3 class="widget-user-username">{{ $item->subject->subject_name }}</h3>
                                        <h5 class="widget-user-desc"><i class="fa fa-university"></i> {{ $item->schoolClass->class_name }}</h5>
                                    </div>
                                </div>

                                <div class="box-footer no-padding">
                                    <ul class="nav nav-stacked">
                                        <li>
                                            <a href="#">
                                                Subject Code
                                                <span class="pull-right badge bg-gray">{{ $item->subject->subject_code ?? 'N/A' }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Enrolled Students
                                                <span class="pull-right badge bg-aqua">Active</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('teacher.marks.manage', $item->id) }}" class="btn-manage text-blue">
                                                <strong><i class="fa fa-calculator text-primary"></i> Gradebook / Marks</strong>
                                                <span class="pull-right text-muted"><i class="fa fa-chevron-right"></i></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="btn-manage text-muted">
                                                <i class="fa fa-calendar"></i> Attendance
                                                <span class="pull-right"><i class="fa fa-chevron-right"></i></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-footer text-center bg-gray-light" style="padding: 5px;">
                                    <small class="text-muted text-uppercase">Academic Year: {{ $item->academic_year ?? date('Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <div class="callout callout-warning" style="background: #fff !important; border-left-color: #f39c12 !important;">
                                <h4 class="text-yellow"><i class="icon fa fa-info-circle"></i> No Load Assigned</h4>
                                <p>You are not currently linked to any subject assignments. If this is an error, please contact the Registrar or System Administrator.</p>
                                <a href="{{ route('teacher.dashboard') }}" class="btn btn-sm btn-warning">Return to Dashboard</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
