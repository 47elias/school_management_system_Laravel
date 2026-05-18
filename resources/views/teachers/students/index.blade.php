<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>My Students | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    <style>
        .student-avatar {
            width: 35px;
            height: 35px;
            border: 2px solid #d2d6de;
            padding: 2px;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
        .search-container {
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-users text-muted"></i> Class Directory
                    <small>Academic Session {{ date('Y') }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">My Class</li>
                </ol>
            </section>

            <section class="content">

                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Current Class</span>
                                <span class="info-box-number">{{ $myClass->class_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-user-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Students</span>
                                <span class="info-box-number">{{ $students->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-primary shadow-sm">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold"><i class="fa fa-list"></i> Registered Students</h3>

                                <div class="box-tools">
                                    <form action="" method="GET" class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" name="search" class="form-control" placeholder="Search by name or ID...">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-search"></i></button>
                                        </span>
                                    </form>
                                </div>
                            </div>

                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr class="bg-gray-light">
                                            <th class="text-center" style="width: 50px">#</th>
                                            <th style="width: 120px">Student ID</th>
                                            <th>Full Name</th>
                                            <th>Gender</th>
                                            <th>Guardian Contact</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width: 200px">Academic Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $index => $student)
                                        <tr>
                                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="label label-default" style="font-size: 11px;">
                                                    {{ $student->student_number ?? $student->id }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex items-center">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name.' '.$student->surname) }}&background=3c8dbc&color=fff&size=35"
                                                         class="img-circle student-avatar shadow-sm" alt="User Image">
                                                    <span class="ml-2 text-bold">{{ $student->name }} {{ $student->surname }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if(strtolower($student->gender) == 'male')
                                                    <i class="fa fa-mars text-blue"></i> Male
                                                @else
                                                    <i class="fa fa-venus text-red"></i> Female
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->guardian_phone)
                                                    <a href="tel:{{ $student->guardian_phone }}" class="btn btn-xs btn-default btn-flat">
                                                        <i class="fa fa-phone text-success"></i> {{ $student->guardian_phone }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small"><em>Not Provided</em></span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($student->status ?? 'active') == 'active' ? 'bg-green' : 'bg-red' }}">
                                                    {{ strtoupper($student->status ?? 'active') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('teacher.students.show', $student->id) }}"
                                                       class="btn btn-info btn-sm btn-flat" title="View Academic Profile">
                                                        <i class="fa fa-user"></i> View
                                                    </a>
                                                    <a href="#" class="btn btn-warning btn-sm btn-flat" title="Record Marks">
                                                        <i class="fa fa-pencil"></i> Marks
                                                    </a>
                                                    <a href="#" class="btn btn-default btn-sm btn-flat" title="Attendance Record">
                                                        <i class="fa fa-calendar-check-o"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center" style="padding: 60px;">
                                                <img src="https://cdn-icons-png.flaticon.com/512/5058/5058432.png" style="width: 80px; opacity: 0.5;">
                                                <h4 class="text-muted mt-3">No students found in this class</h4>
                                                <p class="text-gray small">Assigned students will appear here once registered by the administrator.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="box-footer">
                                <div class="pull-left">
                                    <span class="text-sm text-gray text-uppercase">End of List</span>
                                </div>
                                <div class="pull-right">
                                    {{-- Pagination if needed --}}
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
</body>
</html>
