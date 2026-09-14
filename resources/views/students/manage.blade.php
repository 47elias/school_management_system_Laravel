<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    
    <style>
        :root {
            --brand-primary: #3c8dbc;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }

        /* Lock down html and body completely to kill the outer/window scrollbar */
        html, body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-light) !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            height: 100vh !important;
            max-height: 100vh !important;
            display: flex;
            flex-direction: column;
            overflow: hidden !important;
        }

        /* The content wrapper fills the rest of the viewport and becomes the ONLY scrollable area */
        .content-wrapper {
            background-color: var(--bg-light) !important;
            flex: 1 1 auto !important;
            height: calc(100vh - 100px) !important; /* Subtract header/footer height */
            overflow-y: auto !important; /* Internal scrollbar ONLY inside content area */
            overflow-x: hidden !important;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto;
        }

        .main-footer {
            flex-shrink: 0;
            z-index: 1000;
        }

        .table-vcenter td { vertical-align: middle !important; padding: 14px 16px !important; }
        .id-badge { font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 11px; padding: 4px 8px; border-radius: 6px; }
        .student-name { font-size: 14px; font-weight: 700; color: #1e293b; white-space: nowrap; }
        
        .box { 
            border-radius: 14px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02); 
            border-top: 3px solid var(--brand-primary); 
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            margin-bottom: 30px;
        }

        .box-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; }
        .box-body { padding: 25px; }

        #studentTable { width: 100% !important; font-size: 13px; color: #334155; }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px; border: 1px solid var(--border-color); padding: 6px 12px; height: 38px;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px; border: 1px solid var(--border-color); padding: 4px 8px; height: 38px;
        }

        .nowrap { white-space: nowrap; }
        .btn-action { margin: 0 2px; border-radius: 6px !important; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid var(--border-color); }
        .btn-action:hover { background: #f1f5f9; }
        
        .face-preview { max-width: 100%; border-radius: 8px; border: 2px solid var(--border-color); }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 25px 25px 15px 25px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 24px; margin: 0;">
                    <i class="fa fa-users text-primary" style="margin-right: 6px;"></i> Student Management 
                    <small style="display: block; color: #64748b; font-weight: 500; font-size: 13px; margin-top: 4px;">Registry & Records Database</small>
                </h1>
                <ol class="breadcrumb" style="top: 20px;">
                    <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Students</li>
                </ol>
            </section>

            <section class="content" style="padding: 20px 25px;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check-circle"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 700; color: #1e293b; font-size: 16px; line-height: 32px;">Student Database Directory</h3>
                        <div class="box-tools">
                            <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm btn-flat" style="border-radius: 8px; font-weight: 700; padding: 8px 16px;">
                                <i class="fa fa-user-plus" style="margin-right: 5px;"></i> REGISTER NEW STUDENT
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="studentTable" class="table table-striped table-hover table-vcenter">
                                <thead>
                                    <tr style="background: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                        <th class="nowrap" style="border: none;">Student ID</th>
                                        <th style="border: none;">Full Name</th>
                                        <th style="border: none;">Gender</th>
                                        <th style="border: none;">Level</th>
                                        <th style="border: none;">Biometrics</th>
                                        <th style="border: none;">Status</th>
                                        <th class="text-center" style="border: none;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                    <tr>
                                        <td><span class="label label-default id-badge" style="background: #e2e8f0; color: #334155;">{{ $student->student_number }}</span></td>
                                        <td><div class="student-name">{{ $student->surname }}, {{ $student->name }}</div></td>
                                        <td>{{ $student->gender }}</td>
                                        <td><span class="badge" style="background: #eff6ff; color: #1d4ed8; font-weight: 600; padding: 5px 10px; border-radius: 6px;">{{ $student->grade }}</span></td>
                                        <td class="text-center nowrap">
                                            <a href="{{ route('students.enroll_face', $student->id) }}" class="btn btn-xs btn-success btn-flat" style="border-radius: 6px; font-weight: 600; padding: 4px 10px;" title="Enroll Face">
                                                <i class="fa fa-camera"></i> Enroll
                                            </a>
                                            <button type="button" class="btn btn-xs btn-info btn-flat view-face-btn"
                                                    data-id="{{ $student->id }}"
                                                    data-name="{{ $student->name }}"
                                                    style="border-radius: 6px; font-weight: 600; padding: 4px 10px;"
                                                    title="View Face Data">
                                                <i class="fa fa-user-circle-o"></i> View
                                            </button>
                                        </td>
                                        <td>
                                            <span class="label label-{{ $student->status == 'active' ? 'success' : 'danger' }}" style="padding: 5px 10px; border-radius: 6px; font-weight: 600;">
                                                {{ strtoupper($student->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center nowrap">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-xs btn-action view-profile-btn" data-id="{{ $student->id }}" title="View Profile"><i class="fa fa-eye" style="color: #8b5cf6;"></i></button>
                                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-default btn-xs btn-action" title="Edit Profile"><i class="fa fa-edit" style="color: #3b82f6;"></i></a>
                                                <button type="button" class="btn btn-default btn-xs btn-action delete-student-btn" data-id="{{ $student->id }}" data-name="{{ $student->name }}" title="Delete Record"><i class="fa fa-trash" style="color: #ef4444;"></i></button>
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
        
        @include('layouts.footer')
    </div>

    {{-- MODAL FOR PROFILE & FACE VIEW --}}
    <div class="modal fade" id="dataModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header" style="background: #3c8dbc; color: white; padding: 18px 25px;">
                    <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8;">&times;</button>
                    <h4 class="modal-title" id="modalTitle" style="font-weight: 700;">Details</h4>
                </div>
                <div class="modal-body" id="modalContent" style="padding: 25px;"></div>
            </div>
        </div>
    </div>

    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentTable').DataTable({
                "pageLength": 10,
                "language": {
                    "search": "Filter Records:"
                }
            });

            // Profile View AJAX
            $('.view-profile-btn').on('click', function() {
                $('#modalTitle').text('Student Profile');
                $('#modalContent').html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>');
                $('#modalContent').load('/students/' + $(this).data('id') + '/profile-data');
                $('#dataModal').modal('show');
            });

            // View Face Data Modal
            $('.view-face-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#modalTitle').text('Biometric Data: ' + name);

                $('#modalContent').html(`
                    <div class="text-center">
                        <img src="/students/${id}/view-face" class="face-preview" style="max-height: 250px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('img/default-avatar.png') }}';">
                        <p class="text-muted" style="margin-top:15px; font-weight: 500;">Stored Biometric Signature</p>
                    </div>
                `);
                $('#dataModal').modal('show');
            });

            // Delete Confirmation Handler
            $('.delete-student-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                if(confirm("Are you sure you want to permanently delete student '" + name + "'? This action cannot be undone.")) {
                    $('#delete-form-' + id).submit();
                }
            });
        });
    </script>
</body>
</html>