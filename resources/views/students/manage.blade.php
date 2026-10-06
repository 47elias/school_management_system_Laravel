<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students | Registry</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    @include('components.adminlte')
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    
    <!-- Tailwind CSS with Preflight Disabled to protect AdminLTE layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { primary: '#3c8dbc' } }
                }
            }
        }
    </script>

    <style>
        /* Only apply custom styling to the inner elements, leaving AdminLTE's wrapper alone */
        body { font-family: 'Inter', sans-serif; background-color: #ecf0f5; }
        
        .custom-box {
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            border-top: 4px solid var(--brand-primary);
            margin-bottom: 2rem;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            outline: none;
            background: #fff;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        table.dataTable thead th { border-bottom: none !important; }
        table.dataTable.no-footer { border-bottom: 1px solid #e2e8f0 !important; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper" style="background-color: #f8fafc;">
            <section class="content-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fa fa-users text-blue-600"></i> Student Management
                        </h1>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #64748b; margin: 0.25rem 0 0 0;">Registry & Records Database</p>
                    </div>
                    <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0; font-size: 0.875rem; font-weight: 500;">
                        <li><a href="/" style="color: #64748b;"><i class="fa fa-dashboard" style="margin-right: 0.25rem;"></i> Home</a></li>
                        <li class="active" style="color: #334155;">Students</li>
                    </ol>
                </div>
            </section>

            <section class="content" style="padding: 1.5rem;">
                
                <?php if(session('success')): ?>
                    <div style="margin-bottom: 1.5rem; border-radius: 0.75rem; background-color: #ecfdf5; padding: 1rem; border: 1px solid #a7f3d0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); display: flex; align-items: flex-start; gap: 1rem;">
                        <i class="icon fa fa-check-circle" style="color: #10b981; font-size: 1.25rem; margin-top: 0.125rem;"></i>
                        <div style="flex: 1;">
                            <h4 style="color: #065f46; font-weight: 700; font-size: 0.875rem; margin: 0;">Success!</h4>
                            <p style="color: #047857; font-size: 0.875rem; margin: 0.25rem 0 0 0;"><?php echo session('success'); ?></p>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #10b981; opacity: 1;">
                            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="custom-box">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <h3 style="font-size: 1.125rem; font-weight: 700; color: #1e293b; margin: 0;">Student Directory</h3>
                        <a href="{{ route('students.create') }}" class="btn btn-primary" style="background-color: #2563eb; border-color: #2563eb; border-radius: 0.5rem; font-weight: 600; padding: 0.5rem 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                            <i class="fa fa-user-plus" style="margin-right: 0.5rem;"></i> REGISTER NEW STUDENT
                        </a>
                    </div>

                    <div style="padding: 1.5rem;">
                        <div class="table-responsive">
                            <table id="studentTable" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr style="background-color: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em;">
                                        <th style="padding: 0.75rem 1rem; border-bottom: none;">Student ID</th>
                                        <th style="padding: 0.75rem 1rem; border-bottom: none;">Full Name</th>
                                        <th style="padding: 0.75rem 1rem; border-bottom: none;">Gender</th>
                                        <th style="padding: 0.75rem 1rem; border-bottom: none;">Level</th>
                                        <th style="padding: 0.75rem 1rem; text-align: center; border-bottom: none;">Biometrics</th>
                                        <th style="padding: 0.75rem 1rem; border-bottom: none;">Status</th>
                                        <th style="padding: 0.75rem 1rem; text-align: center; border-bottom: none;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(isset($students) && count($students) > 0): ?>
                                        <?php foreach ($students as$student): ?>
                                        <tr style="transition: background-color 0.2s;">
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                                <span style="display: inline-block; padding: 0.25rem 0.625rem; background-color: #f1f5f9; color: #334155; font-family: monospace; font-size: 0.75rem; font-weight: 700; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                                                    <?php echo $student->student_number; ?>
                                                </span>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle; font-weight: 700; color: #1e293b;">
                                                <?php echo $student->surname; ?>, <?php echo$student->name; ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle; color: #475569; font-weight: 500;">
                                                <?php echo $student->gender; ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                                <span style="display: inline-block; padding: 0.25rem 0.625rem; background-color: #eff6ff; color: #1d4ed8; font-weight: 600; font-size: 0.75rem; border-radius: 0.375rem;">
                                                    <?php echo $student->grade; ?>
                                                </span>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle; text-align: center; white-space: nowrap;">
                                                <a href="{{ route('students.enroll_face', $student->id) }}" class="btn btn-xs" style="background-color: #d1fae5; color: #047857; font-weight: 700; border-radius: 0.375rem; padding: 0.375rem 0.75rem; margin-right: 0.25rem;">
                                                    <i class="fa fa-camera" style="margin-right: 0.25rem;"></i> Enroll
                                                </a>
                                                <button type="button" class="btn btn-xs view-face-btn" data-id="<?php echo $student->id; ?>" data-name="<?php echo $student->name; ?>" style="background-color: #cffafe; color: #0369a1; font-weight: 700; border-radius: 0.375rem; padding: 0.375rem 0.75rem;">
                                                    <i class="fa fa-user-circle-o" style="margin-right: 0.25rem;"></i> View
                                                </button>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle;">
                                                <?php if($student->status == 'active'): ?>
                                                    <span style="display: inline-flex; items-align: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background-color: #dcfce7; color: #15803d;">
                                                        <span style="width: 0.375rem; height: 0.375rem; border-radius: 50%; background-color: #22c55e; margin-right: 0.375rem; margin-top: 0.3rem;"></span> ACTIVE
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display: inline-flex; items-align: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background-color: #fee2e2; color: #b91c1c;">
                                                        <span style="width: 0.375rem; height: 0.375rem; border-radius: 50%; background-color: #ef4444; margin-right: 0.375rem; margin-top: 0.3rem;"></span> <?php echo strtoupper($student->status); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; vertical-align: middle; text-align: center; white-space: nowrap;">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default btn-sm view-profile-btn" data-id="<?php echo $student->id; ?>" title="View Profile" style="border-radius: 0.375rem; color: #9333ea; border-color: #e2e8f0; background: #fff; margin-right: 2px;">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-default btn-sm" title="Edit Profile" style="border-radius: 0.375rem; color: #2563eb; border-color: #e2e8f0; background: #fff; margin-right: 2px;">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-default btn-sm delete-student-btn" data-id="<?php echo $student->id; ?>" data-name="<?php echo $student->name; ?>" title="Delete Record" style="border-radius: 0.375rem; color: #dc2626; border-color: #e2e8f0; background: #fff;">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                                <form id="delete-form-<?php echo $student->id; ?>" action="{{ route('students.destroy', $student->id) }}" method="POST" style="display: none;">
                                                    @csrf 
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="padding: 2rem 1rem; text-align: center; color: #64748b; font-weight: 500;">
                                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                    <i class="fa fa-folder-open-o" style="font-size: 2.25rem; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                                                    <p style="margin: 0;">No students found in the registry.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>

    <!-- Modals -->
    <div class="modal fade" id="dataModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="margin-top: 10vh;">
            <div class="modal-content" style="border-radius: 0.75rem; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
                <div class="modal-header" style="background-color: #1e293b; padding: 1.25rem; border-bottom: none; display: flex; justify-content: space-between; align-items: center;">
                    <h4 class="modal-title" id="modalTitle" style="color: #ffffff; font-weight: 700; font-size: 1.125rem; margin: 0;">Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #94a3b8; opacity: 1; margin-top: -2px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent" style="padding: 1.5rem; background-color: #ffffff;">
                </div>
            </div>
        </div>
    </div>

    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {$('#studentTable').DataTable({
                "pageLength": 10,
                "responsive": true,
                "language": {
                    "search": "",
                    "searchPlaceholder": "Search records...",
                    "emptyTable": "No data available in table"
                }
            });

            // Apply Tailwind-like styling to DataTables wrapping elements dynamically
            $('.dataTables_filter input').attr('placeholder', 'Search records...').css({'margin-left': '0.5rem'});$('.dataTables_length select').css({'margin': '0 0.5rem'});

            $('.view-profile-btn').on('click', function() {$('#modalTitle').text('Student Profile');
                $('#modalContent').html(`
                    <div style="display: flex; justify-content: center; align-items: center; padding: 3rem 0;">
                        <i class="fa fa-spinner fa-spin fa-2x" style="color: #3b82f6;"></i>
                    </div>
                `);
                $('#modalContent').load('/students/' + $(this).data('id') + '/profile-data');$('#dataModal').modal('show');
            });

            $('.view-face-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');$('#modalTitle').text('Biometric Data: ' + name);

                $('#modalContent').html(`
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem;">
                        <div style="width: 12rem; height: 12rem; border-radius: 0.75rem; border: 4px solid #f1f5f9; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06); overflow: hidden; background-color: #f8fafc;">
                            <img src="/students/${id}/view-face" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('img/default-avatar.png') }}';">
                        </div>
                        <p style="color: #64748b; font-weight: 600; font-size: 0.875rem; margin-top: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Stored Biometric Signature</p>
                    </div>
                `);
                $('#dataModal').modal('show');
            });

            $('.delete-student-btn').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                if(confirm(`Are you sure you want to permanently delete student '${name}'? This action cannot be undone.`)) {
                    $('#delete-form-' + id).submit();
                }
            });
        });
    </script>
</body>
</html>