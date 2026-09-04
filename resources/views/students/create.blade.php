<!DOCTYPE html>
<html>
<head>
    <title>Register Student | {{ env('SCHOOL_ACRONYM') }}</title>
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
            color: #3c8dbc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .auto-gen-box {
            background: #f8f9fb;
            border: 1px dashed #d2d6de;
            padding: 20px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .box-primary { border-top-color: #3c8dbc; }
        .help-block { font-size: 12px; color: #777; }
        .btn-flat { transition: all 0.2s; }
        .btn-flat:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        @include('layouts.layout_separator')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-user-plus text-primary"></i> Student Registration
                    <small>Admission Portal</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('students.index') }}">Students</a></li>
                    <li class="active">Register</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Submission Failed!</h4>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="box box-primary shadow">
                            <form role="form" method="POST" action="{{ route('students.store') }}">
                                @csrf

                                <div class="box-body" style="padding: 30px;">

                                    {{-- Section 1: Academic Placement --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-graduation-cap"></i> Academic Placement
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group {{ $errors->has('term_id') ? 'has-error' : '' }}">
                                            <label>Admission Term <span class="text-red">*</span></label>
                                            <select class="form-control select2" name="term_id" style="width: 100%;" required>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}"
                                                        {{ (old('term_id') == $term->id || (isset($currentTerm) && $currentTerm->id == $term->id)) ? 'selected' : '' }}>
                                                        {{ $term->term_name }} - {{ $term->academic_year }}
                                                        @if(isset($currentTerm) && $currentTerm->id == $term->id) (CURRENT ACTIVE) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="help-block">Billing starts from this term selection.</p>
                                        </div>
                                        <div class="col-md-6 form-group {{ $errors->has('grade') ? 'has-error' : '' }}">
                                            <label>Grade Assignment <span class="text-red">*</span></label>
                                            <select class="form-control" name="grade" required>
                                                <option value="">-- Select Grade --</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->class_name }}" {{ old('grade') == $class->class_name ? 'selected' : '' }}>
                                                        {{ $class->class_name }} ({{ $class->class_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Section 2: Identity --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-id-card-o"></i> Personal Details
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>First Name(s) <span class="text-red">*</span></label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter first names" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Surname <span class="text-red">*</span></label>
                                            <input type="text" class="form-control" name="surname" value="{{ old('surname') }}" placeholder="Enter surname" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        {{-- REPLACED AGE WITH DATE OF BIRTH --}}
                                        <div class="col-md-4 form-group">
                                            <label>Date of Birth <span class="text-red">*</span></label>
                                            <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                        </div>
                                        <div class="col-md-2 form-group">
                                            <label>Gender <span class="text-red">*</span></label>
                                            <select class="form-control" name="gender" required>
                                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>National ID / Birth Certificate <span class="text-red">*</span></label>
                                            <input type="text" class="form-control" name="national_id" value="{{ old('national_id') }}" placeholder="ID number" required>
                                        </div>
                                    </div>

                                    <br>

                                    {{-- Section 3: Contact --}}
                                    <div class="form-section-title">
                                        <i class="fa fa-envelope-o"></i> Contact & Address
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Primary Phone <span class="text-red">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="+263..." required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Emergency Contact</label>
                                            <input type="text" class="form-control" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="Name & Number">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label>Residential Address</label>
                                            <textarea class="form-control" name="address" rows="2" placeholder="House No, Street, Suburb">{{ old('address') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="auto-gen-box">
                                        <div class="row">
                                            <div class="col-sm-1 hidden-xs text-center">
                                                <i class="fa fa-magic text-blue" style="font-size: 40px; margin-top: 5px;"></i>
                                            </div>
                                            <div class="col-sm-11">
                                                <h4 style="margin-top: 0; color: #2b669a;">System Automated Process</h4>
                                                <p class="text-muted" style="margin-bottom: 0; font-size: 13px;">
                                                    A unique <strong>Student Number</strong> and portal account will be created automatically.
                                                    The default login password will be set to the <strong>surname123 in lowercase</strong> provided above.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="box-footer" style="padding: 20px 30px; background: #fafafa; border-top: 1px solid #eee;">
                                    <button type="submit" class="btn btn-primary btn-lg btn-flat">
                                        <i class="fa fa-check-circle"></i> COMPLETE REGISTRATION
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
