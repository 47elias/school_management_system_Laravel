<!DOCTYPE html>
<html>
<head>
    <title>Edit Student | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- AdminLTE CSS & Dependencies --}}
    @include('components.adminlte')

    <style>
        .form-section-title {
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #f39c12; /* Warning/Orange color for Edit mode */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .info-stats-box {
            background: #fcf8e3;
            border: 1px solid #faebcc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .box-warning { border-top-color: #f39c12; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            {{-- Content Header --}}
            <section class="content-header">
                <h1>
                    <i class="fa fa-edit text-warning"></i> Edit Student Profile
                    <small>{{ $student->name }} {{ $student->surname }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('students.index') }}">Students</a></li>
                    <li class="active">Edit Profile</li>
                </ol>
            </section>

            {{-- Main Content --}}
            <section class="content">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Update Failed!</h4>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="box box-warning shadow">
                            <div class="box-header with-border">
                                <h3 class="box-title">Student Reference: <strong>{{ $student->student_number }}</strong></h3>
                                <div class="box-tools pull-right">
                                    <span class="label {{ $student->status == 'active' ? 'label-success' : 'label-danger' }}">
                                        {{ strtoupper($student->status) }}
                                    </span>
                                </div>
                            </div>

                            <form role="form" method="POST" action="{{ route('students.update', $student->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="box-body" style="padding: 30px;">

                                    {{-- Quick Info Ribbon --}}
                                    <div class="info-stats-box">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p style="margin:0"><strong>Portal Username:</strong> {{ $student->email }}</p>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <p style="margin:0"><strong>Registered On:</strong> {{ $student->created_at ? $student->created_at->format('d M Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 1: Academic --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-graduation-cap"></i> Academic Placement
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Class</label>
                                            <select class="form-control" name="grade" required>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->class_name }}" {{ old('grade', $student->grade) == $class->class_name ? 'selected' : '' }}>
                                                        {{ $class->class_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Current Term</label>
                                            <select class="form-control select2" name="term_id" required>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ old('term_id', $student->term_id) == $term->id ? 'selected' : '' }}>
                                                        {{ $term->term_name }} ({{ $term->academic_year }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Account Status</label>
                                            <select class="form-control" name="status">
                                                <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Section 2: Identity --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-user"></i> Personal Information
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>First Name(s)</label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $student->name) }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Surname</label>
                                            <input type="text" class="form-control" name="surname" value="{{ old('surname', $student->surname) }}" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Date of Birth</label>
                                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Gender</label>
                                            <select class="form-control" name="gender">
                                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>National ID / Birth Certificate</label>
                                            <input type="text" class="form-control" name="national_id" value="{{ old('national_id', $student->national_id) }}" required>
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Section 3: Contact --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-phone"></i> Contact Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Guardian Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                                <input type="text" class="form-control" name="phone" value="{{ old('phone', $student->phone) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Emergency Contact</label>
                                            <input type="text" class="form-control" name="emergency_contact" value="{{ old('emergency_contact', $student->emergency_contact) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label>Residential Address</label>
                                            <textarea class="form-control" name="address" rows="2">{{ old('address', $student->address) }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="box-footer" style="padding: 20px 30px; background: #fafafa; border-top: 1px solid #eee;">
                                    <button type="submit" class="btn btn-warning btn-lg btn-flat">
                                        <i class="fa fa-save"></i> UPDATE STUDENT RECORD
                                    </button>
                                    <a href="{{ route('students.index') }}" class="btn btn-default btn-lg btn-flat pull-right">
                                        <i class="fa fa-close"></i> CANCEL
                                    </a>
                                </div>
                            </form>
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
