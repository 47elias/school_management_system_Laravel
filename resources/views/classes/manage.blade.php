<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Manage Classes | School Portal</title>
    @include('components.adminlte')
    <style>
        .box { border-top-width: 3px; }
        .table > tbody > tr > td { vertical-align: middle !important; }
        .btn-group .btn { margin-right: 2px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>School Classes <small>Manage academic class structure</small></h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-plus"></i> Add New Class</h3>
                            </div>
                            <form role="form" method="POST" action="{{ route('classes.store') }}">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Class Name</label>
                                        <input type="text" name="class_name" class="form-control" placeholder="e.g. Grade 7" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Class Code</label>
                                        <input type="text" name="class_code" class="form-control" placeholder="e.g. G7A" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Room Number</label>
                                        <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 10">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat">CREATE CLASS</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Existing Classes</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr class="bg-gray-light">
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Teacher</th>
                                            <th>Room</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        <tr>
                                            <td><span class="badge bg-navy">{{ $class->class_code }}</span></td>
                                            <td><strong>{{ $class->class_name }}</strong></td>
                                            <td>
                                                @if($class->teacher)
                                                    <span class="text-blue"><i class="fa fa-user"></i> {{ $class->teacher->name }}</span>
                                                @else
                                                    <span class="text-muted italic"><small>Not Assigned</small></span>
                                                @endif
                                            </td>
                                            <td>{{ $class->room_number }}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                        Actions <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li><a href="{{ route('classes.edit', $class->id) }}"><i class="fa fa-user-plus"></i> Assign Teacher</a></li>
                                                        <li><a href="{{ route('classes.students', $class->id) }}"><i class="fa fa-users"></i> View Students</a></li>
                                                        <li class="divider"></li>
                                                        <li><a href="#" class="text-danger"><i class="fa fa-trash"></i> Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
