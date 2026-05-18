<!DOCTYPE html>
<html>
<head>
    <title>Assign Subjects</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Class-Subject Assignment</h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-5">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Assign Subjects to Class</h3>
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    {{ session('success') }}
                                </div>
                            @endif

                            <form role="form" method="POST" action="{{ route('classes.assign.store') }}">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Select Class</label>
                                        <select name="class_id" class="form-control select2" style="width: 100%;" required>
                                            <option value="">-- Choose a Class --</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }} ({{ $class->class_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Select Subjects</label>
                                        <div class="well well-sm" style="max-height: 300px; overflow-y: auto; background: #fff;">
                                            @foreach($subjects as $subject)
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}">
                                                    <strong>{{ $subject->subject_code }}</strong> - {{ $subject->subject_name }}
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <p class="help-block">Select all subjects that should be taught in this class.</p>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-success btn-block">Save Assignments</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Current Class Subjects</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Assigned Subjects</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        <tr>
                                            <td><strong>{{ $class->class_name }}</strong></td>
                                            <td>
                                                @if($class->subjects->count() > 0)
                                                    @foreach($class->subjects as $assignedSubject)
                                                        <span class="label label-info" style="display: inline-block; margin-bottom: 2px;">
                                                            {{ $assignedSubject->subject_name }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No subjects assigned</span>
                                                @endif
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
