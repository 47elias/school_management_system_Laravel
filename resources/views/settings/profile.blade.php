<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Admin Settings
                    <small>Update Profile Information</small>
                </h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">

                        {{-- Success Message --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-check"></i> Success!</h4>
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-user"></i> My Profile Details</h3>
                            </div>

                            {{-- Form action matches the DashboardController@updateProfile route --}}
                            <form role="form" method="POST" action="{{ route('admin.update_profile') }}">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-info btn-block">Save Changes</button>
                                </div>
                            </form>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('dashboard') }}" class="text-muted"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('layouts.footer')
</body>
</html>
