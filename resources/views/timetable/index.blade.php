<!DOCTYPE html>
<html>
<head>
    <title>Manage Timetables | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <style>
        .box { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #00a65a; }
        .class-card { transition: transform 0.2s; cursor: pointer; border: none !important; }
        .class-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .small-box h3 { font-size: 28px; font-weight: 700; }
        /* Style for Master Timetable card */
        .master-card { border-top: 3px solid #3c8dbc !important; background: #fff !important; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-calendar text-success"></i> Timetable Management
                    <small>Schedule & Planning</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Timetables</li>
                </ol>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title text-bold">Individual Class Timetables</h3>
                        <div class="box-tools">
                            <a href="{{ route('timetable.index') }}?view=master" class="btn btn-info btn-sm btn-flat">
                                <i class="fa fa-th"></i> MASTER VIEW
                            </a>
                            <a href="{{ route('timetable.create') }}" class="btn btn-success btn-sm btn-flat">
                                <i class="fa fa-plus"></i> ADD NEW SLOT
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            @php
                                // Array of AdminLTE background colors to cycle through
                                $colors = ['bg-aqua', 'bg-green', 'bg-yellow', 'bg-red', 'bg-purple', 'bg-blue', 'bg-maroon', 'bg-navy'];
                            @endphp

                            @forelse($classes as $index => $class)
                            <div class="col-md-3 col-sm-6">
                                {{-- Cycle through the colors array using the loop index --}}
                                <div class="small-box {{ $colors[$index % count($colors)] }} class-card">
                                    <div class="inner">
                                        <h3>{{ $class->class_name }}</h3>
                                        <p>Code: {{ $class->class_code ?? 'N/A' }}</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-university"></i>
                                    </div>
                                    <a href="{{ route('timetable.show', $class->id) }}" class="small-box-footer">
                                        View Timetable <i class="fa fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="col-xs-12 text-center">
                                <div class="well">
                                    <i class="fa fa-info-circle fa-2x text-muted"></i>
                                    <p>No classes found. Please add classes before managing timetables.</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')
</body>
</html>
