<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'SIT') }} | Student Login</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic" rel="stylesheet">

    <style>
        /* Modernized Login Page Styling - Matching Staff Layout */
        body.login-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-box {
            margin: 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 400px;
        }
        .login-box-body {
            padding: 30px;
            border-radius: 8px !important;
            /* Green border for Student Portal */
            border-top: 5px solid #00a65a;
        }
        .school-logo {
            max-width: 250px;
            height: auto;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }
        .school-logo:hover {
            transform: scale(1.05);
        }
        .portal-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .login-box-msg {
            padding: 0 20px 20px 20px;
            color: #777;
        }
        .form-control {
            border-radius: 4px !important;
            height: 45px;
        }
        /* Green Student Button */
        .btn-student {
            background-color: #00a65a;
            border-color: #008d4c;
            color: #fff;
            border-radius: 4px !important;
            height: 45px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-student:hover {
            background-color: #008d4c;
            color: #fff;
        }
        .links-container {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .links-container a {
            display: block;
            margin-bottom: 8px;
            color: #00a65a;
        }
        .links-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-box-body">
            <div class="text-center">
                <img src="{{ asset('images/school_logo.png') }}" class="school-logo" alt="School Logo">
                <div class="portal-title">STUDENT PORTAL</div>
                <p class="login-box-msg">Enter your student number and password to login</p>
            </div>

            <form action="{{ route('student.login.submit') }}" method="post">
                @csrf

                <div class="form-group has-feedback @error('student_number') has-error @enderror">
                    <label class="text-muted small">STUDENT NUMBER</label>
                    <input type="text" name="student_number" class="form-control" placeholder="Enter your Student Number" value="{{ old('student_number') }}" required autofocus>
                    <span class="glyphicon glyphicon-education form-control-feedback"></span>

                    @error('student_number')
                        <span class="text-danger small" style="margin-top: 5px; display: block;">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group has-feedback @error('password') has-error @enderror">
                    <label class="text-muted small">PASSWORD</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>

                    @error('password')
                        <span class="text-danger small" style="margin-top: 5px; display: block;">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-xs-7">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="remember"> &nbsp;Remember Me
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-5">
                        <button type="submit" class="btn btn-student btn-block">
                            SIGN IN <i class="fa fa-sign-in" style="margin-left: 5px;"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div class="links-container">
                <a href="{{ route('student.password.request') }}"><i class="fa fa-unlock-alt"></i> Forgot my password</a>

                <a href="{{ route('login') }}" style="color: #3c8dbc;">
                    <i class="fa fa-user-circle-o"></i> Go to <strong>Staff Login</strong>
                </a>
            </div>
        </div>

        <div class="text-center text-muted small" style="margin-top: 20px;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    @include('components.scripts')
    <script src="{{ asset('adminlte/plugins/iCheck/icheck.min.js') }}"></script>
    <script>
        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
                increaseArea: '20%'
            });
        });
    </script>
</body>
</html>
