<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Staff | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        :root { --primary: #3c8dbc; }
        .content-wrapper { background-color: #f4f7fa !important; }
        .box-modern {
            border-top: 3px solid var(--primary);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .form-group label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
        }
        .btn-modern {
            border-radius: 4px;
            font-weight: 600;
            padding: 8px 20px;
        }
        /* Style for the date input to match AdminLTE look */
        input[type="date"].form-control {
            line-height: 1.42857143;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Staff Management
                <small>Create New System User</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('teachers.index') }}">Staff</a></li>
                <li class="active">Add New</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">

                    {{-- Alert Messages --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Please fix the following:</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="box box-modern">
                        <div class="box-header with-border">
                            <h3 class="box-title text-bold">Staff Registration Form</h3>
                        </div>

                        <form action="{{ route('teachers.store') }}" method="POST" role="form">
                            @csrf
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <label for="name">Full Name</label>
                                            <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter Full Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                            <label for="email">Email address</label>
                                            <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" placeholder="Enter Email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('role') ? 'has-error' : '' }}">
                                            <label for="role">Staff Role</label>
                                            <select name="role" id="role" class="form-control" required>
                                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Access Level</option>
                                                <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                                            <label for="phone_number">Phone Number</label>
                                            <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number') }}" placeholder="+263...">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- Updated from employee_id to national_id --}}
                                        <div class="form-group {{ $errors->has('national_id') ? 'has-error' : '' }}">
                                            <label for="national_id">National ID Number</label>
                                            <input type="text" name="national_id" class="form-control" id="national_id" value="{{ old('national_id') }}" placeholder="63-XXXXXXX-X-XX" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- New DOB Field --}}
                                        <div class="form-group {{ $errors->has('dob') ? 'has-error' : '' }}">
                                            <label for="dob">Date of Birth</label>
                                            <input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('ec_number') ? 'has-error' : '' }}">
                                            <label for="ec_number">EC Number</label>
                                            <input type="text" name="ec_number" class="form-control" id="ec_number" value="{{ old('ec_number') }}" placeholder="EC-9988" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                            <label for="password">Password</label>
                                            <input type="password" name="password" class="form-control" id="password" placeholder="Create Login Password" required>
                                        </div>
                                    </div>
                                </div>

                                <p class="help-block"><i class="fa fa-info-circle"></i> Password must be at least 8 characters. It will be encrypted automatically.</p>
                            </div>

                            <div class="box-footer">
                                <a href="{{ route('teachers.index') }}" class="btn btn-default btn-modern">
                                    <i class="fa fa-arrow-left"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary btn-modern pull-right">
                                    <i class="fa fa-save"></i> Save Staff Member
                                </button>
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
