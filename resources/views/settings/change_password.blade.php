<!DOCTYPE html>
<html>
<head>
    <title>Change Password | {{ env('SCHOOL_ACRONYM') }}</title>
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
                    Security Settings
                    <small>Update Password</small>
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

                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-lock"></i> Change Password</h3>
                            </div>

                            {{-- Action matches your route name for DashboardController --}}
                            <form role="form" method="POST" action="{{ route('admin.update_password') }}">
                                @csrf
                                <div class="box-body">

                                    <div class="form-group">
                                        <label for="old_password">Current Password</label>
                                        <input type="password" name="old_password" class="form-control" id="old_password" placeholder="Enter current password" required>
                                    </div>

                                    <hr>

                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <input type="password" name="password" class="form-control" id="password" placeholder="New password (min 8 characters)" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm new password" required>
                                    </div>

                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Update Security Credentials</button>
                                </div>
                            </form>
                        </div>

                        <div class="text-center">
                            {{-- Link updated to 'dashboard' as requested --}}
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
