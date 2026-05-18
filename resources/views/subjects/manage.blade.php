<!DOCTYPE html>
<html>
    <head>
        <title>Manage Subjects & Assignments</title>
        @include('components.adminlte')
        <style>
            .select2-container .select2-selection--single { height: 34px !important; border-radius: 0; border: 1px solid #d2d6de; }
            .table-vcenter td { vertical-align: middle !important; }
            .box-header .fa { margin-right: 5px; }
        </style>
    </head>
    <body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="wrapper">
            <div class="content-wrapper">
                {{-- Header Section --}}
                <section class="content-header">
                    <h1>
                        Academic Configuration
                        <small>Subjects & Teaching Loads</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Subject Management</li>
                    </ol>
                </section>

                <section class="content">
                    {{-- Alert Notifications --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check"></i> Success!</h4>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- ROW 1: SUBJECT MANAGEMENT --}}
                    <div class="row">
                        {{-- Create Subject Form --}}
                        <div class="col-md-4">
                            <div class="box box-primary shadow">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-book text-blue"></i> New Subject</h3>
                                </div>
                                <form method="POST" action="{{ route('subjects.store') }}">
                                    @csrf
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>Subject Name</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mathematics" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Subject Code</label>
                                            <input type="text" name="subject_code" class="form-control" placeholder="e.g. MATH101" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-7">
                                                <div class="form-group">
                                                    <label>Subject Type</label>
                                                    <select name="type" class="form-control">
                                                        <option value="Core">Core</option>
                                                        <option value="Elective">Elective</option>
                                                        <option value="Practical">Practical</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-5">
                                                <div class="form-group">
                                                    <label>Pass Mark (%)</label>
                                                    <input type="number" name="pass_mark" class="form-control" value="50">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary btn-block btn-flat">
                                            <i class="fa fa-plus-circle"></i> Create Subject
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Subject List --}}
                        <div class="col-md-8">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-list"></i> Defined Subjects</h3>
                                </div>
                                <div class="box-body no-padding table-responsive">
                                    <table class="table table-hover table-vcenter">
                                        <thead>
                                            <tr class="bg-gray-light">
                                                <th style="width: 15%">Code</th>
                                                <th>Subject Name</th>
                                                <th style="width: 15%">Type</th>
                                                <th style="width: 15%">Pass Mark</th>
                                                <th style="width: 10%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                            <tr>
                                                <td><span class="label label-default">{{ $subject->subject_code }}</span></td>
                                                <td><b>{{ $subject->subject_name }}</b></td>
                                                <td>
                                                    @php $labelClass = ['Core' => 'info', 'Elective' => 'warning', 'Practical' => 'success'][$subject->type] ?? 'default'; @endphp
                                                    <span class="text-{{ $labelClass }}"><i class="fa fa-circle-o"></i> {{ $subject->type }}</span>
                                                </td>
                                                <td>{{ $subject->pass_mark }}%</td>
                                                <td class="text-center">
                                                    <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-box-tool text-red" onclick="return confirm('Delete this subject?')">
                                                            <i class="fa fa-trash-o fa-lg"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: TEACHER ASSIGNMENTS --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="box box-success shadow">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-user-plus text-green"></i> Assign Teacher</h3>
                                </div>
                                <form method="POST" action="{{ route('subject-assignments.store') }}">
                                    @csrf
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>Select Teacher</label>
                                            <select name="teacher_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Teacher --</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Subject</label>
                                            <select name="subject_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Subject --</option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }} ({{ $subject->subject_code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Class</label>
                                            <select name="class_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Class --</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Academic Year</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" name="academic_year" class="form-control" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-success btn-block btn-flat">
                                            <i class="fa fa-save"></i> Save Assignment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="box box-warning box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-id-badge"></i> Teaching Loads</h3>
                                </div>
                                <div class="box-body no-padding table-responsive">
                                    <table class="table table-hover table-vcenter table-striped">
                                        <thead>
                                            <tr>
                                                <th>Teacher</th>
                                                <th>Subject</th>
                                                <th>Class Allocation</th>
                                                <th>Academic Session</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assignments as $assign)
                                            <tr>
                                                <td>
                                                    <div class="user-block">
                                                        <span class="username" style="margin-left:0"><a href="#">{{ $assign->teacher->name ?? 'N/A' }}</a></span>
                                                        <span class="description" style="margin-left:0">ID: #{{ $assign->teacher->id ?? '0' }}</span>
                                                    </div>
                                                </td>
                                                <td><span class="text-bold">{{ $assign->subject->subject_name ?? 'N/A' }}</span></td>
                                                <td><span class="label bg-navy">{{ $assign->schoolClass->class_name ?? 'N/A' }}</span></td>
                                                <td><i class="fa fa-clock-o text-muted"></i> {{ $assign->academic_year }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('subject-assignments.destroy', $assign->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-flat btn-default text-red" onclick="return confirm('Remove assignment?')">
                                                            <i class="fa fa-times"></i> Unassign
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fa fa-folder-open-o fa-3x"></i>
                                                        <p>No teaching loads assigned yet.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        @include('layouts.footer')
    </body>
    @include('components.scripts')
</html>
