<!DOCTYPE html>
<html>
<head>
    <title>Manage Students | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <style>
        .table-vcenter td { vertical-align: middle !important; }
        .id-badge { font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 11px; padding: 2px 5px; }
        .student-name { font-size: 14px; font-weight: 700; color: #2c3e50; white-space: nowrap; }
        .box { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #3c8dbc; }
        /* Ensure table fits and auto-resizes */
        #studentTable { width: 100% !important; font-size: 13px; }
        .nowrap { white-space: nowrap; }
        /* Profile Modal Styling */
        .profile-user-img { width: 100px; height: 100px; margin: 0 auto; border: 3px solid #d2d6de; padding: 3px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-users text-primary"></i> Student Management
                    <small>Registry & Records</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Students</li>
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

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title text-bold">Student Database</h3>
                        <div class="box-tools">
                            <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm btn-flat">
                                <i class="fa fa-user-plus"></i> REGISTER NEW STUDENT
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="studentTable" class="table table-bordered table-striped table-hover table-vcenter">
                                <thead>
                                    <tr class="bg-navy">
                                        <th class="nowrap">Student ID</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>DOB</th>
                                        <th class="nowrap">National ID</th>
                                        <th>Level</th>
                                        <th>Guardian Phone</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                    <tr>
                                        <td><span class="label label-default id-badge">{{ $student->student_number }}</span></td>
                                        <td>
                                            <div class="student-name">{{ $student->surname }}, {{ $student->name }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if(strtolower($student->gender) == 'male')
                                                <span class="text-blue" title="Male"><i class="fa fa-male fa-lg"></i> M</span>
                                            @else
                                                <span class="text-maroon" title="Female"><i class="fa fa-female fa-lg"></i> F</span>
                                            @endif
                                        </td>
                                        <td class="nowrap">
                                            {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/y') : 'N/A' }}
                                            <small class="text-muted">({{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age : '?' }}y)</small>
                                        </td>
                                        <td><code class="text-blue">{{ $student->national_id }}</code></td>
                                        <td><span class="badge bg-blue">{{ $student->grade }}</span></td>
                                        <td class="nowrap"><i class="fa fa-phone text-muted"></i> {{ $student->phone }}</td>
                                        <td>
                                            @if($student->status == 'active')
                                                <span class="label label-success" style="font-size: 10px;">ACTIVE</span>
                                            @else
                                                <span class="label label-danger" style="font-size: 10px;">INACTIVE</span>
                                            @endif
                                        </td>
                                        <td class="text-center nowrap">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-xs btn-action view-profile-btn"
                                                        data-id="{{ $student->id }}" title="View Profile">
                                                    <i class="fa fa-eye text-purple"></i>
                                                </button>

                                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-default btn-xs btn-action" title="Edit">
                                                    <i class="fa fa-edit text-blue"></i>
                                                </a>

                                                <button type="button" class="btn btn-default btn-xs btn-action delete-student-btn"
                                                        data-id="{{ $student->id }}" data-name="{{ $student->name }} {{ $student->surname }}" title="Delete">
                                                    <i class="fa fa-trash text-red"></i>
                                                </button>
                                            </div>

                                            <form id="delete-form-{{ $student->id }}" action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
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
    </div>

    {{-- STUDENT PROFILE MODAL --}}
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-user"></i> Student Profile</h4>
                </div>
                <div class="modal-body" id="profileContent">
                    <div class="text-center">
                        <i class="fa fa-refresh fa-spin fa-3x text-muted"></i>
                        <p>Loading Profile...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')

    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with Responsive settings
            var table = $('#studentTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true, // Enables auto resizing
                "order": [[1, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 8 } // Disable ordering on Actions
                ]
            });

            // Handle Profile View
            $('#studentTable').on('click', '.view-profile-btn', function() {
                var id = $(this).data('id');
                $('#profileModal').modal('show');
                $('#profileContent').html('<div class="text-center"><i class="fa fa-refresh fa-spin fa-2x"></i><p>Loading...</p></div>');

                $.ajax({
                    url: '/students/' + id + '/profile-data',
                    method: 'GET',
                    success: function(data) {
                        $('#profileContent').html(data);
                    },
                    error: function() {
                        $('#profileContent').html('<div class="alert alert-danger">Error: Could not load data.</div>');
                    }
                });
            });

            // Handle Delete
            $('#studentTable').on('click', '.delete-student-btn', function(e) {
                e.preventDefault();
                var studentId = $(this).data('id');
                var studentName = $(this).data('name');
                if (confirm('Delete ' + studentName + '?')) {
                    $('#delete-form-' + studentId).submit();
                }
            });
        });
    </script>
</body>
</html>
