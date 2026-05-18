<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Edit Staff | {{ $staff->name }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Edit Staff Member
                    <small>Modify account details for {{ $staff->name }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teachers.index') }}"><i class="fa fa-users"></i> Staff List</a></li>
                    <li class="active">Edit Staff</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">

                        {{-- Validation Errors --}}
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

                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-user-edit"></i> Account Information</h3>
                            </div>

                            <form action="{{ route('teachers.update', $staff->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="name">Full Name</label>
                                            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $staff->name) }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $staff->email) }}" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="role">System Role</label>
                                            <select name="role" class="form-control" id="role">
                                                <option value="teacher" {{ $staff->role == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                                <option value="admin" {{ $staff->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="receptionist" {{ $staff->role == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="phone_number">Phone Number</label>
                                            <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number', $staff->phone_number) }}">
                                        </div>
                                    </div>

                                    <hr>
                                    <h4 class="text-muted">Personal & Employment Details</h4>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="national_id">National ID</label>
                                            <input type="text" name="national_id" class="form-control" id="national_id" value="{{ old('national_id', $staff->national_id) }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="dob">Date of Birth</label>
                                            <input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob', $staff->dob ? $staff->dob->format('Y-m-d') : '') }}" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label for="ec_number">EC Number</label>
                                            <input type="text" name="ec_number" class="form-control" id="ec_number" value="{{ old('ec_number', $staff->ec_number) }}" required>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="callout callout-warning" style="margin-bottom: 5px;">
                                        <h4>Security</h4>
                                        <p>Leave the password field <strong>blank</strong> if you do not want to change it.</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">New Password (Optional)</label>
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Staff Member
                                    </button>
                                    <a href="{{ route('teachers.index') }}" class="btn btn-default pull-right">Cancel</a>
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
