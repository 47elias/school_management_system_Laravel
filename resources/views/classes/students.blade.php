<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Class Roster | {{ $class->class_name }}</title>
    @include('components.adminlte')

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap.min.css">

    <style>
        .box { border-top-width: 3px; }
        /* Professional Row Hover Effect */
        #student-table tbody tr:hover { background-color: #eef6ff !important; transition: 0.2s; }
        .dataTables_wrapper .dt-buttons { margin-bottom: 10px; }
        /* Custom Gender Badge Colors */
        .badge-male { background-color: #0073b7 !important; }
        .badge-female { background-color: #d81b60 !important; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    {{ $class->class_name }}
                    <small class="text-blue">Roster: {{ $class->class_code }}</small>
                </h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-list-ul text-blue"></i> Registered Students</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table id="student-table" class="table table-hover table-striped">
                                    <thead>
                                        <tr class="bg-navy">
                                            <th class="text-white"># Number</th>
                                            <th class="text-white">Surname</th>
                                            <th class="text-white">First Name</th>
                                            <th class="text-white">Gender</th>
                                            <th class="text-white text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($class->students as $student)
                                        <tr>
                                            <td><span class="label bg-gray">{{ $student->student_number }}</span></td>
                                            <td><strong>{{ $student->surname }}</strong></td>
                                            <td>{{ $student->name }}</td>
                                            <td>
                                                @php $gender = strtolower($student->gender ?? 'n/a'); @endphp
                                                <span class="badge {{ $gender == 'male' ? 'badge-male' : ($gender == 'female' ? 'badge-female' : 'bg-gray') }}">
                                                    {{ ucfirst($student->gender ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="#" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-user"></i> View</a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No students assigned to this class yet.</td>
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
                "dom": 'Bfrtip',
                "buttons": [
                    { extend: 'excel', className: 'btn-success' },
                    { extend: 'pdf', className: 'btn-danger' },
                    { extend: 'print', className: 'btn-primary' }
                ]
            });
        });
    </script>
</body>
</html>
