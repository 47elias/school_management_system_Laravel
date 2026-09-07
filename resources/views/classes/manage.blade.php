<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Manage Classes | School Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Scoped Modern UI Updates (Will not affect AdminLTE Footer/Layout) -->
    <style>
        /* Modern Box Styling */
        .content .box { 
            border-radius: 12px; 
            border-top: none; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            margin-bottom: 25px; 
            overflow: hidden; 
            background: #ffffff;
        }
        .content .box-header { 
            border-bottom: 1px solid #f1f5f9; 
            padding: 20px 25px; 
            background: #fff; 
        }
        .content .box-title { 
            font-weight: 800 !important; 
            color: #1e293b; 
            font-size: 18px; 
        }
        
        /* Form Inputs */
        .content .form-group label { 
            font-weight: 700; 
            color: #475569; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 8px; 
        }
        .content .form-control { 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            box-shadow: none; 
            padding: 10px 15px; 
            height: auto; 
            font-size: 15px; 
            transition: all 0.3s ease; 
        }
        .content .form-control:focus { 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
        }
        
        /* Primary Button */
        .content .btn-primary { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            border: none; 
            border-radius: 8px; 
            font-weight: 700; 
            padding: 12px 20px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(59,130,246,0.3); 
        }
        .content .btn-primary:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(59,130,246,0.4); 
        }
        
        /* Table overrides */
        .content .table > tbody > tr > td { 
            vertical-align: middle !important; 
            padding: 16px 20px; 
            border-top: 1px solid #f1f5f9; 
            font-size: 15px; 
            color: #334155; 
        }
        .content .table > thead > tr > th { 
            border-bottom: 2px solid #e2e8f0; 
            color: #64748b; 
            font-weight: 700; 
            padding: 16px 20px; 
            text-transform: uppercase; 
            font-size: 12px; 
            letter-spacing: 0.5px; 
            background: #f8fafc; 
        }
        .content .table-hover > tbody > tr:hover { 
            background-color: #f8fafc; 
        }

        /* Badges & Action Buttons */
        .content .badge-code { 
            background: #e2e8f0; 
            color: #475569; 
            font-family: monospace; 
            font-size: 13px; 
            padding: 6px 10px; 
            border-radius: 6px; 
            font-weight: 600; 
        }
        .content .btn-action { 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 13px; 
            padding: 6px 12px; 
            background: #f8fafc; 
            border: 1px solid #cbd5e1; 
            color: #475569; 
            transition: all 0.2s; 
        }
        .content .btn-action:hover { 
            background: #f1f5f9; 
            color: #1e293b; 
        }
        .content .dropdown-menu { 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            border: 1px solid #e2e8f0; 
            padding: 8px 0; 
        }
        .content .dropdown-menu > li > a { 
            padding: 8px 20px; 
            color: #475569; 
            font-weight: 500; 
        }
        .content .dropdown-menu > li > a:hover { 
            background-color: #f1f5f9; 
            color: #3b82f6; 
        }
        .content .dropdown-menu .divider {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    School Classes
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Manage academic class structure</small>
                </h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif

                <div class="row">
                    <!-- Left Column: Add Class Form -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-plus-circle text-blue"></i> Add New Class</h3>
                            </div>
                            <form role="form" method="POST" action="{{ route('classes.store') }}">
                                @csrf
                                <div class="box-body" style="padding: 25px;">
                                    <div class="form-group">
                                        <label>Class Name</label>
                                        <input type="text" name="class_name" class="form-control" placeholder="e.g. Grade 7" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Class Code</label>
                                        <input type="text" name="class_code" class="form-control" placeholder="e.g. G7A" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>Room Number</label>
                                        <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 10">
                                    </div>
                                </div>
                                <div class="box-footer" style="padding: 20px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-save" style="margin-right: 5px;"></i> CREATE CLASS
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Existing Classes Table -->
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-list text-green"></i> Existing Classes</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Code</th>
                                            <th style="width: 25%;">Name</th>
                                            <th style="width: 30%;">Teacher</th>
                                            <th style="width: 15%;">Room</th>
                                            <th class="text-center" style="width: 15%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        <tr>
                                            <td>
                                                <span class="badge-code">{{ $class->class_code }}</span>
                                            </td>
                                            <td>
                                                <strong style="color: #1e293b; font-size: 15px;">{{ $class->class_name }}</strong>
                                            </td>
                                            <td>
                                                @if($class->teacher)
                                                    <span class="label" style="background: #eff6ff; color: #3b82f6; font-size: 13px; padding: 6px 10px; border: 1px solid #bfdbfe;">
                                                        <i class="fa fa-user" style="margin-right: 4px;"></i> {{ $class->teacher->name }}
                                                    </span>
                                                @else
                                                    <span style="color: #94a3b8; font-style: italic; font-size: 13px;">
                                                        <i class="fa fa-exclamation-circle"></i> Not Assigned
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span style="color: #475569; font-weight: 500;">{{ $class->room_number ?? '-' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-action dropdown-toggle" data-toggle="dropdown">
                                                        Options <span class="caret" style="margin-left: 4px;"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li>
                                                            <a href="{{ route('classes.edit', $class->id) }}">
                                                                <i class="fa fa-user-plus text-blue" style="width: 20px;"></i> Assign Teacher
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('classes.students', $class->id) }}">
                                                                <i class="fa fa-users text-green" style="width: 20px;"></i> View Students
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="#" class="text-danger" style="color: #ef4444;">
                                                                <i class="fa fa-trash" style="width: 20px;"></i> Delete Class
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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