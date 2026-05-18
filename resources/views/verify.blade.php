<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'SIT') }} | Reset Password</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        body.login-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-box { margin: 0; width: 450px; }
        .login-box-body {
            padding: 30px;
            border-radius: 8px !important;
            border-top: 5px solid #3c8dbc;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .portal-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            text-align: center;
        }
        .form-control { border-radius: 4px !important; height: 40px; }
        .btn-flat { border-radius: 4px !important; height: 45px; font-weight: 600; }
        .section-divider {
            margin: 20px 0 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #777;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-box-body">
            <div class="portal-title">RESET ACCOUNT PASSWORD</div>

            @if(session('error'))
                <div class="alert alert-danger small" style="margin-top:15px;">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('password.update.final') }}" method="post">
                @csrf

                <div class="section-divider">1. Identify Your Account</div>

                <div class="form-group has-feedback @error('ec_number') has-error @enderror">
                    <label class="small">EC NUMBER</label>
                    <input type="text" name="ec_number" class="form-control" value="{{ old('ec_number') }}" required placeholder="e.g. 1001">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback @error('id_number') has-error @enderror">
                    <label class="small">NATIONAL ID NUMBER</label>
                    <input type="text" name="id_number" class="form-control" value="{{ old('id_number') }}" required placeholder="e.g. 12123456B12">
                    <span class="glyphicon glyphicon-tag form-control-feedback"></span>
                </div>

                <div class="section-divider">2. Set New Credentials</div>

                <div class="form-group has-feedback @error('password') has-error @enderror">
                    <label class="small">NEW PASSWORD</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min. 8 characters">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @error('password') <span class="help-block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group has-feedback">
                    <label class="small">CONFIRM NEW PASSWORD</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat password">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                </div>

                <div class="row" style="margin-top: 25px;">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">
                            UPDATE PASSWORD <i class="fa fa-save" style="margin-left: 5px;"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center" style="margin-top: 20px;">
                <a href="{{ route('login') }}" class="text-muted small"><i class="fa fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>
    @include('components.scripts')
</body>
</html>
