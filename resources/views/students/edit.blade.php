<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Edit Student | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- AdminLTE CSS & Dependencies --}}
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-warning: #f59e0b;
            --brand-warning-hover: #d97706;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; overflow-x: hidden; }
        .content-wrapper { background-color: var(--bg-light) !important; min-height: 100vh; }

        .form-section-title {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
            margin-bottom: 20px;
            margin-top: 10px;
            color: #d97706; 
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .info-stats-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            color: #92400e;
            font-size: 14px;
        }

        .box-warning { 
            border-top-color: #f59e0b; 
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
        }

        .form-control {
            border-radius: 8px !important;
            border: 1px solid var(--border-color) !important;
            height: 42px !important;
            box-shadow: none !important;
            font-size: 14px !important;
            color: #1e293b !important;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
        }

        textarea.form-control {
            height: auto !important;
        }

        label {
            font-weight: 600 !important;
            color: #475569 !important;
            font-size: 13px !important;
            margin-bottom: 6px !important;
        }

        .btn-warning-custom {
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 700;
            transition: all 0.2s;
        }

        .btn-warning-custom:hover {
            background: #d97706;
            color: white;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        @include('layouts.layout_separator')

        <div class="content-wrapper">
            {{-- Content Header --}}
            <section class="content-header" style="padding: 25px 25px 15px 25px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 24px; margin: 0;">
                    <i class="fa fa-edit text-warning" style="margin-right: 6px;"></i> Edit Student Profile
                    <small style="display: block; color: #64748b; font-weight: 500; font-size: 13px; margin-top: 4px;">{{ $student->name }} {{ $student->surname }}</small>
                </h1>
                <ol class="breadcrumb" style="top: 20px;">
                    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('students.index') }}">Students</a></li>
                    <li class="active">Edit Profile</li>
                </ol>
            </section>

            {{-- Main Content --}}
            <section class="content" style="padding: 20px 25px;">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" style="border-radius: 10px; border: none;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Update Failed!</h4>
                                <ul style="margin-bottom: 0; padding-left: 15px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="box box-warning">
                            <div class="box-header with-border" style="padding: 20px 25px; background: #fff; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                                <h3 class="box-title" style="font-weight: 700; color: #1e293b; font-size: 16px;">Student Reference: <strong>{{ $student->student_number }}</strong></h3>
                                <div class="box-tools pull-right" style="top: 15px;">
                                    <span class="label {{ $student->status == 'active' ? 'label-success' : 'label-danger' }}" style="padding: 6px 12px; font-size: 11px; font-weight: 600; border-radius: 6px;">
                                        {{ strtoupper($student->status) }}
                                    </span>
                                </div>
                            </div>

                            <form role="form" method="POST" action="{{ route('students.update', $student->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="box-body" style="padding: 30px; background: #fff;">

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
                                            <select class="form-control" name="term_id" required>
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

                                    <div style="height: 10px;"></div>

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
                                        <div class="col-md-4 form-group">
                                            <label>Date of Birth</label>
                                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Gender</label>
                                            <select class="form-control" name="gender">
                                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 form-group">
                                            <label>National ID / Birth Certificate</label>
                                            <input type="text" class="form-control" name="national_id" value="{{ old('national_id', $student->national_id) }}" required>
                                        </div>
                                    </div>

                                    <div style="height: 10px;"></div>

                                    {{-- Section 3: Contact --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-phone"></i> Contact Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Guardian Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background: #f8fafc; border-color: var(--border-color); border-radius: 8px 0 0 8px;"><i class="fa fa-phone"></i></span>
                                                <input type="text" class="form-control" name="phone" value="{{ old('phone', $student->phone) }}" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" required>
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

                                <div class="box-footer" style="padding: 20px 30px; background: #fafafa; border-top: 1px solid #f1f5f9; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                                    <button type="submit" class="btn btn-warning-custom btn-flat">
                                        <i class="fa fa-save" style="margin-right: 5px;"></i> UPDATE STUDENT RECORD
                                    </button>
                                    <a href="{{ route('students.index') }}" class="btn btn-default btn-flat pull-right" style="border-radius: 8px; padding: 10px 20px; font-weight: 600; border-color: #cbd5e1;">
                                        <i class="fa fa-close" style="margin-right: 5px;"></i> CANCEL
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