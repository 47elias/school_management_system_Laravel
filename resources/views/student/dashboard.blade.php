@extends('layouts.student')

@section('content')
<section class="content-header">
    <h1>
        Academic Portal
        <small>{{ env('SCHOOL_ACRONYM') }} v{{ env('PORTAL_VERSION') }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</section>

<section class="content">
    {{-- TOP STATS ROW --}}
    <div class="row">
        {{-- GRADE BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Grade / Level</span>
                    <span class="info-box-number">{{ $student->grade }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description text-bold">{{ env('SCHOOL_NAME') }}</span>
                </div>
            </div>
        </div>

        {{-- FEE STATUS BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box {{ $calculatedBalance > 0 ? 'bg-red' : 'bg-green' }}">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Fee Balance</span>
                    <span class="info-box-number">{{ env('CURRENCY_SYMBOL') }}{{ number_format($calculatedBalance, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $paymentPercentage }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ number_format($paymentPercentage, 0) }}% Cleared
                    </span>
                </div>
            </div>
        </div>

        {{-- TERM BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Term</span>
                    {{-- Priority logic: uses current term or fallbacks to student's assigned term --}}
                    <span class="info-box-number">{{ $currentTerm->term_name ?? $student->term->name ?? 'N/A' }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">Academic Year {{ date('Y') }}</span>
                </div>
            </div>
        </div>

        {{-- PERFORMANCE BOX --}}
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Academic Status</span>
                    <span class="info-box-number">Results Ready</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <a href="{{ route('student.results') }}" style="color: white; font-weight: bold;">
                            VIEW REPORT <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN: PROFILE --}}
        <div class="col-md-3">
            {{-- Profile Image Box --}}
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <div style="position: relative; text-align: center;">
                        <img class="profile-user-img img-responsive img-circle"
                             src="{{ asset($avatar) }}"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=3c8dbc&color=fff'"
                             alt="Student profile"
                             style="width: 100px; height: 100px; object-fit: cover;">
                        <span class="label label-success" style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); border: 2px solid white;">ACTIVE</span>
                    </div>

                    <h3 class="profile-username text-center text-bold">{{ $student->name }} {{ $student->surname }}</h3>
                    <p class="text-muted text-center"><i class="fa fa-id-card-o"></i> {{ $student->student_number }}</p>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Email</b> <span class="pull-right text-sm text-primary">{{ $student->email ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Gender</b> <span class="pull-right">{{ $student->gender }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Contact</b> <span class="pull-right">{{ $student->phone ?? 'N/A' }}</span>
                        </li>
                    </ul>

                    <a href="{{ route('student.fees') }}" class="btn btn-primary btn-block btn-flat text-bold">
                        <i class="fa fa-file-text-o"></i> FINANCIAL STATEMENT
                    </a>
                </div>
            </div>

            {{-- School Contact Box --}}
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title text-bold">Campus Info</h3>
                </div>
                <div class="box-body">
                    <strong><i class="fa fa-map-marker margin-r-5 text-primary"></i> Address</strong>
                    <p class="text-muted">{{ env('SCHOOL_ADDRESS') }}</p>
                    <hr>
                    <strong><i class="fa fa-phone margin-r-5 text-primary"></i> Phone</strong>
                    <p class="text-muted">{{ env('SCHOOL_PHONE') }}</p>
                    <hr>
                    <strong><i class="fa fa-globe margin-r-5 text-primary"></i> Website</strong>
                    <p class="text-muted"><a href="http://{{ env('SCHOOL_WEBSITE') }}" target="_blank">{{ env('SCHOOL_WEBSITE') }}</a></p>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: CONTENT TABS --}}
        <div class="col-md-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#activity" data-toggle="tab"><i class="fa fa-clock-o"></i> Timeline</a></li>
                    <li><a href="#personal" data-toggle="tab"><i class="fa fa-user"></i> Full Profile</a></li>
                    <li><a href="#guardian" data-toggle="tab"><i class="fa fa-users"></i> Emergency Contact</a></li>
                </ul>
                <div class="tab-content">
                    {{-- TIMELINE TAB --}}
                    <div class="active tab-pane" id="activity">
                        <ul class="timeline timeline-inverse">
                            <li class="time-label"><span class="bg-blue">{{ date('d M. Y') }}</span></li>

                            {{-- Finance Timeline Item --}}
                            <li>
                                <i class="fa fa-money bg-{{ $calculatedBalance > 0 ? 'red' : 'green' }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> {{ date('H:i') }}</span>
                                    <h3 class="timeline-header text-bold"><a href="#">Financial Overview</a></h3>
                                    <div class="timeline-body">
                                        Your current account balance for the active term is
                                        <span class="text-{{ $calculatedBalance > 0 ? 'red' : 'green' }} text-bold">
                                            {{ env('CURRENCY_SYMBOL') }}{{ number_format($calculatedBalance, 2) }}
                                        </span>.
                                        Please ensure all payments are made before the end of the month.
                                    </div>
                                    <div class="timeline-footer">
                                        <a href="{{ route('student.fees') }}" class="btn btn-xs btn-default">Details</a>
                                    </div>
                                </div>
                            </li>

                            {{-- Support Timeline Item --}}
                            <li>
                                <i class="fa fa-envelope bg-blue"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header no-border">
                                        <a href="#">Support:</a> Need help? Contact us at {{ env('SCHOOL_EMAIL') }}
                                    </h3>
                                </div>
                            </li>

                            <li><i class="fa fa-clock-o bg-gray"></i></li>
                        </ul>
                    </div>

                    {{-- PERSONAL INFO TAB --}}
                    <div class="tab-pane" id="personal">
                        <div class="box-body no-padding">
                            <h4 class="text-blue" style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">Registration Particulars</h4>
                            <table class="table table-striped">
                                <tr><th width="30%">Student ID</th><td>{{ $student->student_number }}</td></tr>
                                <tr><th>Full Name</th><td>{{ $student->name }} {{ $student->surname }}</td></tr>
                                <tr><th>Enrollment Grade</th><td>{{ $student->grade }}</td></tr>
                                <tr><th>Primary Mobile</th><td>{{ $student->phone ?? 'N/A' }}</td></tr>
                                <tr><th>Email Address</th><td>{{ $student->email ?? 'N/A' }}</td></tr>
                                <tr><th>Residential Address</th><td>{{ $student->address ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    {{-- GUARDIAN TAB --}}
                    <div class="tab-pane" id="guardian">
                        <div class="box-body text-center" style="padding: 40px 10px;">
                            <div style="font-size: 60px; color: #d2d6de; margin-bottom: 10px;">
                                <i class="fa fa-user-secret"></i>
                            </div>
                            <h4 class="text-bold">Guardian / Next of Kin</h4>
                            <p class="text-muted">For emergency notifications or payment queries, we contact:</p>
                            <h2 class="text-primary text-bold" style="letter-spacing: 1px;">
                                {{ $student->parent_contact ?? 'N/A' }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WELCOME CALLOUT --}}
            <div class="callout callout-info" style="border-left: 5px solid #0073b7 !important; background-color: #f4f7f9 !important; color: #444 !important;">
                <h4 class="text-bold text-blue">Welcome back, {{ $student->name }}!</h4>
                <p>Welcome to the <strong>{{ env('SCHOOL_NAME') }}</strong> Academic Portal. You can track your academic progress, monitor your financial status, and update your personal records here.</p>
            </div>
        </div>
    </div>
</section>

<style>
    .info-box-number { font-size: 22px !important; }
    .profile-user-img { border: 3px solid #d2d6de; padding: 3px; transition: all 0.3s ease; }
    .profile-user-img:hover { border-color: #3c8dbc; }
    .nav-tabs-custom > .nav-tabs > li.active { border-top-color: #3c8dbc; }
    .list-group-unbordered > .list-group-item { border-radius: 0; padding-left: 0; padding-right: 0; }
    .text-bold { font-weight: 700; }
</style>
@endsection
