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
        #studentTable { width: 100% !important; font-size: 13px; }
        .nowrap { white-space: nowrap; }
        .btn-action { margin: 0 1px; }
        .face-preview { max-width: 100%; border-radius: 4px; border: 2px solid #ddd; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.layout_separator')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1><i class="fa fa-users text-primary"></i> Student Management <small>Registry & Records</small></h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h4><i class="icon fa fa-check"></i> Success!</h4>{{ session('success') }}</div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title text-bold">Student Database</h3>
                        <div class="box-tools">
                            <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-user-plus"></i> REGISTER NEW STUDENT</a>
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
                                        <th>Level</th>
                                        <th>Biometrics</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                    <tr>
                                        <td><span class="label label-default id-badge">{{ $student->student_number }}</span></td>
                                        <td><div class="student-name">{{ $student->surname }}, {{ $student->name }}</div></td>
                                        <td>{{ $student->gender }}</td>
                                        <td><span class="badge bg-blue">{{ $student->grade }}</span></td>
                                        <td class="text-center nowrap">
                                            <a href="{{ route('students.enroll_face', $student->id) }}" class="btn btn-xs btn-success btn-flat" title="Enroll Face">
                                                <i class="fa fa-camera"></i> Enroll
                                            </a>
                                            <button type="button" class="btn btn-xs btn-info btn-flat view-face-btn"
                                                    data-id="{{ $student->id }}"
                                                    data-name="{{ $student->name }}"
                                                    title="View Face Data">
                                                <i class="fa fa-user-circle-o"></i> View
                                            </button>
                                        </td>
                                        <td>
                                            <span class="label label-{{ $student->status == 'active' ? 'success' : 'danger' }}">{{ strtoupper($student->status) }}</span>
                                        </td>
                                        <td class="text-center nowrap">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-xs btn-action view-profile-btn" data-id="{{ $student->id }}"><i class="fa fa-eye text-purple"></i></button>
                                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-default btn-xs btn-action"><i class="fa fa-edit text-blue"></i></a>
                                                <button type="button" class="btn btn-default btn-xs btn-action delete-student-btn" data-id="{{ $student->id }}" data-name="{{ $student->name }}"><i class="fa fa-trash text-red"></i></button>
                                            </div>
                                            <form id="delete-form-{{ $student->id }}" action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
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

    {{-- MODAL FOR PROFILE & FACE VIEW --}}
    <div class="modal fade" id="dataModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Details</h4>
                </div>
                <div class="modal-body" id="modalContent"></div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentTable').DataTable();

            // Profile View
            $('.view-profile-btn').on('click', function() {
                $('#modalTitle').text('Student Profile');
                $('#modalContent').load('/students/' + $(this).data('id') + '/profile-data');
                $('#dataModal').modal('show');
            });

            // View Face Data
            $('.view-face-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#modalTitle').text('Biometric Data: ' + name);

                // Pointing to the route we defined in our controller logic
                $('#modalContent').html(`
                    <div class="text-center">
                        <img src="/students/${id}/view-face" class="face-preview" onerror="this.onerror=null;this.src='{{ asset('img/default-avatar.png') }}';">
                        <p class="text-muted" style="margin-top:10px;">Stored Biometric Signature</p>
                    </div>
                `);
                $('#dataModal').modal('show');
            });
        });
    </script>
</body>
</html>
