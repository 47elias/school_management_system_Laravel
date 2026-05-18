<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>Student Portal | {{ config('app.name') }}</title>

    @include('components.adminlte')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">

    <style>
        /* --- MODERN SIDEBAR & LAYOUT FIXES --- */
        .main-sidebar {
            padding-top: 50px !important;
            background-color: #1e282c !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .user-panel {
            padding: 20px 15px;
            background: #1a2226;
            border-bottom: 1px solid #2c3b41;
            margin-bottom: 5px;
        }

        /* Sidebar Menu Styling */
        .sidebar-menu > li.header {
            color: #4b646f !important;
            background: #1a2226 !important;
            padding: 12px 20px !important;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .sidebar-menu > li > a {
            border-left: 3px solid transparent;
            color: #b8c7ce !important;
            transition: all 0.2s ease;
        }

        .sidebar-menu > li.active > a {
            color: #ffffff !important;
            background: #161d20 !important;
            border-left-color: #3c8dbc !important;
        }

        /* Fixed Header height overlap */
        .content-wrapper { background-color: #f4f7f9 !important; }

        /* Dropdown Shadow */
        .navbar-nav > .user-menu > .dropdown-menu { border: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<div class="wrapper">

    <header class="main-header">
        <a href="{{ route('student.dashboard') }}" class="logo">
            <span class="logo-mini"><b>S</b>P</span>
            <span class="logo-lg"><b>STUDENT</b>PORTAL</span>
        </a>

        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ Auth::guard('student')->user()->gender == 'Female' ? asset('adminlte/dist/img/avatar3.png') : asset('adminlte/dist/img/avatar5.png') }}" class="user-image" alt="User">
                            <span class="hidden-xs">{{ Auth::guard('student')->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <img src="{{ Auth::guard('student')->user()->gender == 'Female' ? asset('adminlte/dist/img/avatar3.png') : asset('adminlte/dist/img/avatar5.png') }}" class="img-circle" alt="User">
                                <p>
                                    {{ Auth::guard('student')->user()->name }}
                                    <small>Student Portal v2.6</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ route('student.change_password') }}" class="btn btn-default btn-flat">Security</a>
                                </div>
                                <div class="pull-right">
                                    <a href="#" class="btn btn-danger btn-flat"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Sign Out
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{ Auth::guard('student')->user()->gender == 'Female' ? asset('adminlte/dist/img/avatar3.png') : asset('adminlte/dist/img/avatar5.png') }}"
                         class="img-circle" style="border: 2px solid #3c8dbc; width: 45px; height: 45px;">
                </div>
                <div class="pull-left info">
                    <p>{{ Auth::guard('student')->user()->name }}</p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
                <div class="clearfix"></div>
            </div>

            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">MAIN NAVIGATION</li>
                <li class="{{ Request::is('student/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('student.dashboard') }}"><i class="fa fa-th-large"></i> <span>Dashboard</span></a>
                </li>
                <li class="{{ Request::is('student/results') ? 'active' : '' }}">
                    <a href="{{ route('student.results') }}"><i class="fa fa-graduation-cap"></i> <span>My Results</span></a>
                </li>
                <li class="{{ Request::is('student/fees') ? 'active' : '' }}">
                    <a href="{{ route('student.fees') }}"><i class="fa fa-money"></i> <span>Fees & Finance</span></a>
                </li>

                {{-- Added AI Support Chat --}}
                <li class="{{ Request::is('student/ai-chat*') ? 'active' : '' }}">
                    <a href="{{ route('student.ai_chat') }}">
                        <i class="fa fa-comments text-aqua"></i> <span>AI Support Chat</span>
                        <span class="pull-right-container">
                            <small class="label pull-right bg-aqua">AI</small>
                        </span>
                    </a>
                </li>

                <li class="header">ACCOUNT SETTINGS</li>
                <li class="{{ Request::is('student/change-password') ? 'active' : '' }}">
                    <a href="{{ route('student.change_password') }}"><i class="fa fa-lock"></i> <span>Security</span></a>
                </li>
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off text-red"></i> <span class="text-red">Logout</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <footer class="main-footer">
        <div class="pull-right hidden-xs"><b>Secure</b> Portal</div>
        <strong>Copyright &copy; {{ date('Y') }} {{ config('app.name') }}</strong>
    </footer>

    <form id="logout-form" action="{{ route('student.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

</body>
</html>


