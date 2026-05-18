<!DOCTYPE html>
<html>
<head>
    <title>Manage Classes</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>School Classes</h1>
            </section>

            <section class="content">
                {{-- Success Message Alert --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Add New Class</h3>
                            </div>
                            <form role="form" method="POST" action="{{ route('classes.store') }}">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Class Name</label>
                                        <input type="text" name="class_name" class="form-control" placeholder="e.g. Grade 1" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Class Code</label>
                                        <input type="text" name="class_code" class="form-control" placeholder="e.g. G1A" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Room Number</label>
                                        <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 10">
                                    </div>
                                    <div class="form-group">
                                        <label>Capacity</label>
                                        <input type="number" name="capacity" class="form-control" value="100">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Create Class</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Existing Classes</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Teacher</th> {{-- Added Column --}}
                                            <th>Room</th>
                                            <th>Status</th>
                                            <th style="width: 100px">Action</th> {{-- Added Column --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        <tr>
                                            <td><strong>{{ $class->class_code }}</strong></td>
                                            <td>{{ $class->class_name }}</td>
                                            {{-- Display Assigned Teacher Name or 'Not Assigned' --}}
                                            <td>
                                                @if($class->teacher)
                                                    <span class="text-blue"><i class="fa fa-user"></i> {{ $class->teacher->name }}</span>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>{{ $class->room_number }}</td>
                                            <td>
                                                <span class="label {{ $class->status == 'active' ? 'label-success' : 'label-danger' }}">
                                                    {{ ucfirst($class->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- Button to go to the assignment page --}}
                                                <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-xs btn-info" title="Assign Teacher">
                                                    <i class="fa fa-edit"></i> Assign
                                                </a>
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
    </div>
    @include('layouts.footer')
    @include('components.scripts')
</body>
</html>
