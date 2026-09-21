<!DOCTYPE html>
<html lang="en">
<head>
    <title>Class Timetable Management</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    
    <style>
        :root {
            --brand-primary: #3c8dbc;
            --bg-light: #f4f6f9; /* Standard AdminLTE background */
            --border-color: #d2d6de;
        }

        html, body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-light) !important;
            height: 100vh !important;
            overflow: hidden !important;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            overflow: hidden !important;
        }

        .content-wrapper {
            background-color: var(--bg-light) !important;
            flex: 1 1 auto !important;
            height: calc(100vh - 50px) !important; /* Adjust for AdminLTE header height */
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-bottom: 30px;
        }

        .table-vcenter td { 
            vertical-align: middle !important; 
            padding: 12px 14px !important; 
        }

        .box { 
            border-radius: 4px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
            border-top: 3px solid var(--brand-primary); 
            background: #fff;
            margin-bottom: 20px;
        }

        .box-header { padding: 15px 20px; border-bottom: 1px solid #f4f4f4; }
        .box-body { padding: 20px; }

        #timetableTable { width: 100% !important; font-size: 13px; color: #444; }
        
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 4px; 
            border: 1px solid #ccc; 
            padding: 6px 12px; 
            height: 34px;
            font-size: 13px;
            background: #fff;
        }

        .nowrap { white-space: nowrap; }

        .btn-action { 
            margin: 0 2px; 
            border-radius: 4px !important; 
            width: 32px !important; 
            height: 32px !important; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            background: #f4f4f4; 
            border: 1px solid #ddd; 
        }
        .btn-action:hover { background: #e7e7e7; }

        @media (max-width: 768px) {
            .box-header { display: flex; flex-direction: column; gap: 10px; }
            .box-header .box-tools { position: static; width: 100%; display: flex; gap: 6px; flex-wrap: wrap; }
            .box-header .box-tools a, .box-header .box-tools button { flex: 1; text-align: center; }
            .box-header .class-dropdown-container { width: 100%; margin-left: 0 !important; margin-top: 5px; }
            .box-header .class-dropdown-container select { width: 100% !important; }
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header" style="padding: 15px 20px;">
                <h1>
                    <i class="fa fa-calendar text-blue"></i> Timetable Management
                    <small>Class Schedules & Period Allocations</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Timetable</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content" style="padding: 0 20px;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 4px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" style="border-radius: 4px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 600;">Scheduled Slots Directory</h3>
                        
                        <!-- Quick Class Selector Dropdown -->
                        <div class="class-dropdown-container" style="display: inline-block; margin-left: 20px;">
                            <select id="classSelector" class="form-control input-sm" style="display: inline-block; width: 180px; border-radius: 4px; border: 1px solid #ccc; height: 32px; font-size: 13px;" onchange="if(this.value) window.location.href='/admin/timetable/class/' + this.value;">
                                <option value="">-- Jump to Class View --</option>
                                @foreach(\App\Models\SchoolClass::where('status', 'active')->get() as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="box-tools pull-right" style="top: 10px;">
                            <form action="{{ route('timetable.generate') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Warning: Generating a new automated timetable will clear and overwrite existing schedules. Proceed?');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm btn-flat" style="font-weight: 600;">
                                    <i class="fa fa-magic"></i> AUTO-GENERATE
                                </button>
                            </form>
                            <a href="{{ route('timetable.create') }}" class="btn btn-primary btn-sm btn-flat" style="font-weight: 600;">
                                <i class="fa fa-plus"></i> ADD SLOT
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="timetableTable" class="table table-bordered table-striped table-hover table-vcenter">
                                <thead>
                                    <tr style="background: #f4f4f4; color: #333; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                        <th>Class</th>
                                        <th>Day</th>
                                        <th>Time Slot</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Room</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timetables as $slot)
                                    <tr>
                                        <td>
                                            @if($slot->class_id)
                                                <a href="{{ route('timetable.show', $slot->class_id) }}" title="View Class Schedule">
                                                    <span class="label label-primary" style="font-size: 11px; padding: 4px 8px; display: inline-block;">
                                                        {{ $slot->schoolClass?->class_name ?? 'N/A' }} <i class="fa fa-external-link" style="margin-left: 3px; font-size: 9px;"></i>
                                                    </span>
                                                </a>
                                            @else
                                                <span class="label label-default" style="font-size: 11px; padding: 4px 8px;">N/A</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $slot->day }}</strong></td>
                                        <td class="nowrap"><i class="fa fa-clock-o text-muted"></i> {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                        <td>{{ $slot->subject?->subject_name ?? 'N/A' }}</td>
                                        <td>{{ $slot->teacher?->name ?? 'Unassigned' }}</td>
                                        <td><span class="label label-default" style="color: #333;">{{ $slot->room_number ?? 'N/A' }}</span></td>
                                        <td class="text-center nowrap">
                                            <div class="btn-group">
                                                <a href="{{ route('timetable.edit', $slot->id) }}" class="btn btn-default btn-xs btn-action" title="Edit Slot"><i class="fa fa-edit text-blue"></i></a>
                                                <button type="button" class="btn btn-default btn-xs btn-action delete-slot-btn" data-id="{{ $slot->id }}" title="Delete Slot"><i class="fa fa-trash text-red"></i></button>
                                            </div>
                                            <form id="delete-form-{{ $slot->id }}" action="{{ route('timetable.destroy', $slot->id) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#timetableTable').DataTable({
                "pageLength": 10,
                "responsive": true,
                "language": {
                    "search": "Filter Slots:"
                }
            });

            $('.delete-slot-btn').on('click', function() {
                var id = $(this).data('id');
                if(confirm("Are you sure you want to remove this timetable slot?")) {
                    $('#delete-form-' + id).submit();
                }
            });
        });
    </script>
</body>
</html>