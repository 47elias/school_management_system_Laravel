<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Teacher Dashboard | SIT</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        .small-box { border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .inner-action-icon {
            font-size: 40px;
            float: right;
            opacity: 0.3;
        }
        .small-box:hover .inner-action-icon {
            opacity: 0.5;
            transition: 0.3s;
        }
        .widget-user-2 .widget-user-header { padding: 15px; border-top-right-radius: 3px; border-top-left-radius: 3px; }
        .table > tbody > tr > td { vertical-align: middle; }
        .progress-group .progress-text { font-weight: 600; margin-bottom: 5px; }
        .box { border-top-width: 3px; }
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
                    Teacher Portal
                    <small>Overview for {{ Auth::user()->name }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Dashboard</li>
                </ol>
            </section>

            {{-- MAIN CONTENT --}}
            <section class="content">

                {{-- ROW 1: ENHANCED QUICK STATS --}}
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-aqua-active">
                            <div class="inner">
                                <h3>{{ $studentCount }}</h3>
                                <p>Total Students</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-ios-people"></i>
                            </div>
                            <a href="{{ route('teacher.my_class') }}" class="small-box-footer">My Class List <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green-active">
                            <div class="inner">
                                <h3>{{ $examCount }}</h3>
                                <p>Active Assessments</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-document-text"></i>
                            </div>
                            <a href="{{ route('teacher.exams.index') }}" class="small-box-footer">View Exams <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow-active">
                            <div class="inner">
                                <h3>{{ $myClass->class_code ?? 'None' }}</h3>
                                <p>Form Class</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-university"></i>
                            </div>
                            <a href="{{ route('teacher.my_class') }}" class="small-box-footer">Class Management <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple-active">
                            <div class="inner">
                                <h3>{{ $assignedSubjects->count() }}</h3>
                                <p>Subjects Taught</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-ios-book"></i>
                            </div>
                            <a href="{{ route('teacher.subjects') }}" class="small-box-footer">Academic Load <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- LEFT COLUMN --}}
                    <div class="col-md-8">

                        {{-- ENHANCED QUICK ACTIONS --}}
                        <div class="box box-solid bg-gray-light">
                            <div class="box-header with-border">
                                <h3 class="box-title text-black"><i class="fa fa-bolt"></i> Quick Workflow</h3>
                            </div>
                            <div class="box-body">
                                <div class="row text-center">
                                    <div class="col-xs-4">
                                        <a href="{{ route('teacher.exams.create') }}" class="btn btn-app btn-block bg-navy margin-bottom">
                                            <i class="fa fa-plus-square"></i> Create Exam
                                        </a>
                                    </div>
                                    <div class="col-xs-4">
                                        <a href="#" class="btn btn-app btn-block bg-olive margin-bottom">
                                            <span class="badge bg-yellow">!</span>
                                            <i class="fa fa-calendar-check-o"></i> Daily Roll Call
                                        </a>
                                    </div>
                                    <div class="col-xs-4">
                                        <a href="#" class="btn btn-app btn-block bg-maroon margin-bottom">
                                            <i class="fa fa-bullhorn"></i> Parents Notice
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ENHANCED STUDENT LIST --}}
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users text-blue"></i> Form Class: {{ $myClass->class_name ?? 'Not Assigned' }}</h3>
                                <div class="box-tools pull-right">
                                    <span class="label label-primary">{{ $classStudents->count() ?? 0 }} Registered</span>
                                </div>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th># Number</th>
                                            <th>Full Name</th>
                                            <th>Status</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($classStudents as $student)
                                        <tr>
                                            <td><code class="text-primary">{{ $student->student_number }}</code></td>
                                            <td>
                                                <strong>{{ $student->name }} {{ $student->surname }}</strong>
                                            </td>
                                            <td><span class="label label-success">Enrolled</span></td>
                                            <td class="text-right">
                                                <a href="{{ route('teacher.students.show', $student->id) }}" class="btn btn-xs btn-default btn-flat border-blue"><i class="fa fa-eye"></i> View</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center p-20 text-muted">No students found in your assigned class.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TEACHING LOAD WITH PROGRESS INDICATORS --}}
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-graduation-cap"></i> Grading & Subject Load</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    @forelse($assignedSubjects as $assignment)
                                    <div class="col-md-6">
                                        <div class="well well-sm bg-white" style="border-left: 3px solid #00c0ef">
                                            <h4 style="margin-top: 0">{{ $assignment->subject->name ?? 'Subject' }}</h4>
                                            <p class="text-muted"><i class="fa fa-university"></i> Class: {{ $assignment->schoolClass->class_name }}</p>
                                            <a href="{{ route('teacher.marks.manage', $assignment->id) }}" class="btn btn-sm btn-info btn-flat"><i class="fa fa-edit"></i> Record Marks</a>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-xs-12 text-center text-muted">No subject assignments found.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="col-md-4">

                        {{-- TEACHER PROFILE CARD --}}
                        <div class="box box-widget widget-user-2 shadow">
                            <div class="widget-user-header bg-blue-active">
                                <div class="widget-user-image">
                                    <img class="img-circle" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=fff&color=0073b7" alt="User Avatar">
                                </div>
                                <h3 class="widget-user-username">{{ Auth::user()->name }}</h3>
                                <h5 class="widget-user-desc">Authenticated Academic Staff</h5>
                            </div>
                            <div class="box-footer no-padding">
                                <ul class="nav nav-stacked">
                                    <li><a href="#">Staff ID <span class="pull-right badge bg-blue">{{ Auth::user()->id }}</span></a></li>
                                    <li><a href="#">Email <span class="pull-right text-muted">{{ Auth::user()->email }}</span></a></li>
                                    <li><a href="{{ route('teacher.profile') }}" class="text-center bg-gray-light"><strong>Update My Profile</strong></a></li>
                                </ul>
                            </div>
                        </div>

                        {{-- ACADEMIC CALENDAR PROGRESS --}}
                        <div class="box box-danger">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold"><i class="fa fa-calendar"></i> Term Timeline</h3>
                            </div>
                            <div class="box-body">
                                <div class="progress-group">
                                    <span class="progress-text">Term 1 Completion</span>
                                    <span class="progress-number"><b>8</b>/12 Weeks</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-red progress-bar-striped" style="width: 65%"></div>
                                    </div>
                                </div>
                                <p class="text-sm text-muted text-center margin-top">Upcoming: Mid-term Assessments</p>
                            </div>
                        </div>

                        {{-- SYSTEM NOTIFICATIONS --}}
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-bell-o"></i> Recent Alerts</h3>
                            </div>
                            <div class="box-body no-padding">
                                <ul class="nav nav-pills nav-stacked">
                                    <li><a href="#"><i class="fa fa-envelope-o text-blue"></i> New Exam Template Ready
                                        <span class="label label-primary pull-right">New</span></a></li>
                                    <li><a href="#"><i class="fa fa-info-circle text-red"></i> Submit Final Grades by Friday</a></li>
                                    <li><a href="#"><i class="fa fa-users text-green"></i> 3 New Students joined class</a></li>
                                </ul>
                            </div>
                            <div class="box-footer text-center">
                                <a href="#" class="uppercase">View All Alerts</a>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
