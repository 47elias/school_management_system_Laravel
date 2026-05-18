@extends('layouts.student')

@section('content')
<section class="content-header">
    <h1>Security Settings <small>Update Account Password</small></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    {{ session('success') }}
                </div>
            @endif

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lock"></i> Change Password</h3>
                </div>

                <form action="{{ route('student.update_password') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <p class="text-muted">Ensure your account is using a long, random password to stay secure.</p>

                        <div class="form-group @error('current_password') has-error @enderror">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" class="form-control" id="current_password" placeholder="Enter old password">
                            @error('current_password')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group @error('new_password') has-error @enderror">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" class="form-control" id="new_password" placeholder="Minimum 6 characters">
                            @error('new_password')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation" placeholder="Repeat new password">
                        </div>
                    </div>

                    <div class="box-footer text-right">
                        <button type="submit" class="btn btn-primary btn-flat">
                            <i class="fa fa-save"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

            <div class="callout callout-warning" style="background: #fff !important; border-left-color: #f39c12 !important; color: #444 !important;">
                <h4><i class="fa fa-shield"></i> Security Tip</h4>
                <p>Never share your password with anyone. Administrators will never ask for your password via email or phone.</p>
            </div>
        </div>
    </div>
</section>
@endsection
