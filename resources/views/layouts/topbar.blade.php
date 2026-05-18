<header class="main-header">
    {{-- Original Logo Colors Kept --}}
    <a href="{{ url('/dashboard') }}" class="logo">
        <span class="logo-mini"><b>{{ substr(env('SCHOOL_ACRONYM', 'S'), 0, 1) }}</b></span>
        <span class="logo-lg"><b>{{ env('SCHOOL_ACRONYM', 'SMS') }}</b></span>
    </a>

    <nav class="navbar navbar-static-top">
        {{-- Sidebar Toggle --}}
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                {{-- Notifications --}}
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning" style="border-radius: 50%; padding: 2px 5px;">1</span>
                    </a>
                    <ul class="dropdown-menu" style="border-radius: 8px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2); overflow: hidden;">
                        <li class="header" style="font-weight: 700;">You have 1 notification</li>
                        <li>
                            <ul class="menu">
                                <li>
                                    <a href="#" style="padding: 12px 15px;">
                                        <i class="fa fa-info-circle text-aqua"></i> Welcome to the management system
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                {{-- User Profile Dropdown --}}
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ asset('adminlte/dist/img/avatar5.png') }}" class="user-image" alt="User Image" style="border: 1px solid rgba(255,255,255,0.2);">
                        <span class="hidden-xs">{{ Auth::user()?->name }}</span>
                    </a>
                    <ul class="dropdown-menu" style="border-radius: 8px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.25); width: 280px; margin-top: 1px;">
                        {{-- User Header - Keeping the Original Blue --}}
                        <li class="user-header">
                            <img src="{{ asset('adminlte/dist/img/avatar5.png') }}" class="img-circle" alt="User Image" style="border: 3px solid rgba(255,255,255,0.2);">
                            <p>
                                {{ Auth::user()->name }}
                                <small style="display: block; margin-top: 5px; opacity: 0.8;">Administrator</small>
                                <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                            </p>
                        </li>

                        {{-- Action Buttons --}}
                        <li class="user-footer" style="background: #f9f9f9; padding: 15px;">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat" style="border-radius: 4px; font-weight: 600;">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" class="btn btn-default btn-flat"
                                   style="border-radius: 4px; font-weight: 600; color: #d9534f;"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="#"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
    /* Ensure the original skin colors remain dominant */
    .main-header .navbar {
        min-height: 50px;
    }
    /* Adding subtle shadows and smoothing transitions without changing color codes */
    .navbar-nav > .user-menu > .dropdown-menu {
        border-top-right-radius: 0 !important;
        border-top-left-radius: 0 !important;
    }
    .user-header {
        height: auto !important;
        padding: 20px !important;
    }
</style>
