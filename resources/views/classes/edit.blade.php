<!DOCTYPE html>
<html>
<head>
    <title>Assign Teacher - {{ $class?->class_name }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Manage Class
                    <small>Assign Form Teacher</small>
                </h1>
            </section>

            <section class="content">
                {{-- Error Alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    {{-- Form Column - Matches 'Add New Class' UI --}}
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Assign Teacher</h3>
                            </div>

                            <form role="form" method="POST" action="{{ route('classes.update', $class?->id) }}">
                                @csrf
                                @method('PUT')

                                {{-- Hidden field for validation requirements --}}
                                <input type="hidden" name="class_name" value="{{ $class?->class_name }}">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Class Name</label>
                                        {{-- Null-safe operator (?->) fixes the IDE warning --}}
                                        <input type="text" class="form-control" value="{{ $class?->class_name }}" disabled>
                                    </div>

                                    <div class="form-group {{ $errors->has('teacher_id') ? 'has-error' : '' }}">
                                        <label for="teacher_id">Form Teacher</label>
                                        <select name="teacher_id" class="form-control select2" style="width: 100%;">
                                            <option value="">-- No Teacher Assigned --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ ($class?->teacher_id == $teacher->id) ? 'selected' : '' }}>
                                                    {{ $teacher->name }} (ID: {{ $teacher->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Save Assignment</button>
                                    <a href="{{ route('classes.index') }}" class="btn btn-default btn-block">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Information Column --}}
                    <div class="col-md-8">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Assignment Information</h3>
                            </div>
                            <div class="box-body">
                                <p>You are assigning a teacher to <strong>{{ $class?->class_name }}</strong>.</p>
                                <ul>
                                    <li>The assigned teacher will be able to view students in this class.</li>
                                    <li>They can manage attendance and academic reports.</li>
                                    <li>Only one teacher can be the primary "Form Teacher" per class.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')

    <script>
        $(document).ready(function() {
            // Re-initialize select2 if used
            if ($('.select2').length > 0) {
                $('.select2').select2();
            }
        });
    </script>
</body>
</html>
