<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Class Roster | {{ $class->class_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap.min.css">

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
            background-color: #f8fafc !important; 
        }

        /* Badges */
        .content .badge-student-no {
            background: #f8fafc;
            color: #475569;
            font-family: monospace;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        .content .badge-gender {
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .content .badge-male { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
        .content .badge-female { background: #fdf2f8; color: #ec4899; border: 1px solid #fbcfe8; }
        .content .badge-other { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

        /* Action Buttons */
        .content .btn-view {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #3b82f6;
            border-radius: 6px;
            font-weight: 600;
            padding: 6px 15px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .content .btn-view:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #2563eb;
        }

        /* DataTables Tweaks */
        .content .dataTables_wrapper .dt-buttons { margin-bottom: 15px; }
        .content .dataTables_wrapper .dt-buttons .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 6px 15px;
            margin-right: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: none;
        }
        .content .dataTables_filter input {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            box-shadow: none;
        }
        .content .dataTables_filter input:focus {
            border-color: #3b82f6;
            outline: none;
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
                    {{ $class->class_name }}
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Roster: {{ $class->class_code }}</small>
                </h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-users text-blue"></i> Registered Students</h3>
                            </div>
                            <div class="box-body table-responsive" style="padding: 20px;">
                                <table id="student-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th># Number</th>
                                            <th>Surname</th>
                                            <th>First Name</th>
                                            <th>Gender</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($class->students as $student)
                                        <tr>
                                            <td>
                                                <span class="badge-student-no">{{ $student->student_number }}</span>
                                            </td>
                                            <td>
                                                <strong style="color: #1e293b; font-size: 16px; text-transform: uppercase;">{{ $student->surname }}</strong>
                                            </td>
                                            <td>
                                                <span style="color: #334155;">{{ $student->name }}</span>
                                            </td>
                                            <td>
                                                @php $gender = strtolower($student->gender ?? 'n/a'); @endphp
                                                <span class="badge-gender {{ $gender == 'male' ? 'badge-male' : ($gender == 'female' ? 'badge-female' : 'badge-other') }}">
                                                    {{ ucfirst($student->gender ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="#" class="btn btn-sm btn-view">
                                                    <i class="fa fa-user" style="margin-right: 4px;"></i> View Profile
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 60px 20px;">
                                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                    <i class="fa fa-user-times" style="font-size: 35px; color: #94a3b8;"></i>
                                                </div>
                                                <h4 style="font-weight: 700; color: #475569;">No Students Found</h4>
                                                <p style="color: #94a3b8;">No students are assigned to this class yet.</p>
                                            </td>
                                        </tr>
                                        @endforelse
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

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#student-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "language": {
                    "search": "Filter Roster:"
                },
                "dom": '<"row"<"col-sm-6"B><"col-sm-6"f>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-5"i><"col-sm-7"p>>',
                "buttons": [
                    { 
                        extend: 'excel', 
                        className: 'btn btn-sm', 
                        style: 'background: #10b981; color: white;',
                        text: '<i class="fa fa-file-excel-o"></i> Export Excel' 
                    },
                    { 
                        extend: 'pdf', 
                        className: 'btn btn-sm', 
                        style: 'background: #ef4444; color: white;',
                        text: '<i class="fa fa-file-pdf-o"></i> Export PDF' 
                    },
                    { 
                        extend: 'print', 
                        className: 'btn btn-sm', 
                        style: 'background: #3b82f6; color: white;',
                        text: '<i class="fa fa-print"></i> Print Roster' 
                    }
                ]
            });
            
            /* Apply custom colors directly to the Datatable buttons after initialization */
            $('.buttons-excel').css({'background-color': '#10b981', 'color': 'white'});
            $('.buttons-pdf').css({'background-color': '#ef4444', 'color': 'white'});
            $('.buttons-print').css({'background-color': '#3b82f6', 'color': 'white'});
        });
    </script>
</body>
</html>