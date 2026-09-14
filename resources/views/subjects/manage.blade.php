<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Manage Subjects & Assignments | {{ env('SCHOOL_ACRONYM') }}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @include('components.adminlte')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            :root {
                --brand-primary: #3c8dbc;
                --brand-success: #10b981;
                --bg-light: #f8fafc;
                --border-color: #e2e8f0;
            }

            body { 
                font-family: 'Inter', sans-serif !important; 
                background-color: var(--bg-light) !important; 
                overflow-x: hidden; 
            }

            .content-wrapper { 
                background-color: var(--bg-light) !important; 
                min-height: 100vh; 
            }

            /* Modern Card Styling */
            .box-modern {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid var(--border-color);
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
                margin-bottom: 25px;
                overflow: hidden;
            }

            .box-modern.box-primary-top { border-top: 3px solid #3c8dbc; }
            .box-modern.box-success-top { border-top: 3px solid #10b981; }
            .box-modern.box-warning-top { border-top: 3px solid #f59e0b; }

            .box-header-modern {
                padding: 20px 25px;
                border-bottom: 1px solid #f1f5f9;
                background: #ffffff;
            }

            .box-header-modern h3 {
                font-weight: 700;
                color: #1e293b;
                font-size: 16px;
                margin: 0;
            }

            .form-control {
                border-radius: 8px !important;
                border: 1px solid var(--border-color) !important;
                height: 42px !important;
                box-shadow: none !important;
                font-size: 14px !important;
                color: #1e293b !important;
                transition: all 0.2s ease;
            }

            .form-control:focus {
                border-color: var(--brand-primary) !important;
                box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.15) !important;
            }

            label {
                font-weight: 600 !important;
                color: #475569 !important;
                font-size: 13px !important;
                margin-bottom: 6px !important;
            }

            .select2-container .select2-selection--single {
                height: 42px !important;
                border-radius: 8px !important;
                border: 1px solid var(--border-color) !important;
                padding: 6px 12px;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px !important;
            }

            .table-vcenter td { vertical-align: middle !important; padding: 14px 20px !important; }
            
            .btn-flat-custom {
                border-radius: 8px !important;
                font-weight: 700;
                padding: 10px 20px;
                transition: all 0.2s;
            }

            .badge-custom {
                padding: 6px 12px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 12px;
            }
        </style>
    </head>
    <body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
        <div class="wrapper">
            @include('layouts.topbar')
            @include('layouts.sidebar')

            <div class="content-wrapper">
                {{-- Header Section --}}
                <section class="content-header" style="padding: 25px 25px 15px 25px;">
                    <h1 style="font-weight: 800; color: #1e293b; font-size: 24px; margin: 0;">
                        Academic Configuration
                        <small style="display: block; color: #64748b; font-weight: 500; font-size: 13px; margin-top: 4px;">Subjects & Teaching Loads Management</small>
                    </h1>
                    <ol class="breadcrumb" style="top: 20px;">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Subject Management</li>
                    </ol>
                </section>

                <section class="content" style="padding: 20px 25px;">
                    {{-- Alert Notifications --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" style="border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check-circle"></i> Success!</h4>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" style="border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(239,68,68,0.15);">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- ROW 1: SUBJECT MANAGEMENT --}}
                    <div class="row">
                        {{-- Create Subject Form --}}
                        <div class="col-md-4">
                            <div class="box-modern box-primary-top">
                                <div class="box-header-modern">
                                    <h3><i class="fa fa-book text-blue" style="margin-right: 8px;"></i> New Subject</h3>
                                </div>
                                <form method="POST" action="{{ route('subjects.store') }}">
                                    @csrf
                                    <div class="box-body" style="padding: 25px;">
                                        <div class="form-group">
                                            <label>Subject Name</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background: #f8fafc; border-color: var(--border-color); border-radius: 8px 0 0 8px;"><i class="fa fa-tag"></i></span>
                                                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mathematics" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Subject Code</label>
                                            <input type="text" name="subject_code" class="form-control" placeholder="e.g. MATH101" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-7">
                                                <div class="form-group">
                                                    <label>Subject Type</label>
                                                    <select name="type" class="form-control">
                                                        <option value="Core">Core</option>
                                                        <option value="Elective">Elective</option>
                                                        <option value="Practical">Practical</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-5">
                                                <div class="form-group">
                                                    <label>Pass Mark (%)</label>
                                                    <input type="number" name="pass_mark" class="form-control" value="50">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer" style="padding: 20px 25px; background: #fafafa; border-top: 1px solid #f1f5f9;">
                                        <button type="submit" class="btn btn-primary btn-block btn-flat-custom">
                                            <i class="fa fa-plus-circle" style="margin-right: 5px;"></i> Create Subject
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Subject List --}}
                        <div class="col-md-8">
                            <div class="box-modern">
                                <div class="box-header-modern">
                                    <h3><i class="fa fa-list text-muted" style="margin-right: 8px;"></i> Defined Subjects</h3>
                                </div>
                                <div class="box-body no-padding table-responsive">
                                    <table class="table table-hover table-vcenter">
                                        <thead>
                                            <tr style="background: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                                <th style="width: 15%; border: none;">Code</th>
                                                <th style="border: none;">Subject Name</th>
                                                <th style="width: 20%; border: none;">Type</th>
                                                <th style="width: 15%; border: none;">Pass Mark</th>
                                                <th style="width: 10%; border: none;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subjects as $subject)
                                            <tr>
                                                <td><span class="label label-default" style="background: #e2e8f0; color: #334155; padding: 5px 8px; border-radius: 6px; font-weight: 700;">{{ $subject->subject_code }}</span></td>
                                                <td><b style="color: #1e293b;">{{ $subject->subject_name }}</b></td>
                                                <td>
                                                    @php 
                                                        $badgeBg = ['Core' => '#eff6ff', 'Elective' => '#fffbeb', 'Practical' => '#ecfdf5'][$subject->type] ?? '#f1f5f9';
                                                        $badgeColor = ['Core' => '#1d4ed8', 'Elective' => '#b45309', 'Practical' => '#047857'][$subject->type] ?? '#475569';
                                                    @endphp
                                                    <span class="badge-custom" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; display: inline-block;">
                                                        <i class="fa fa-circle-o" style="margin-right: 3px;"></i> {{ $subject->type }}
                                                    </span>
                                                </td>
                                                <td><span style="font-weight: 600; color: #334155;">{{ $subject->pass_mark }}%</span></td>
                                                <td class="text-center">
                                                    <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-default btn-xs text-red" style="border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);" onclick="return confirm('Delete this subject?')">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: TEACHER ASSIGNMENTS --}}
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-4">
                            <div class="box-modern box-success-top">
                                <div class="box-header-modern">
                                    <h3><i class="fa fa-user-plus text-green" style="margin-right: 8px;"></i> Assign Teacher</h3>
                                </div>
                                <form method="POST" action="{{ route('subject-assignments.store') }}">
                                    @csrf
                                    <div class="box-body" style="padding: 25px;">
                                        <div class="form-group">
                                            <label>Select Teacher</label>
                                            <select name="teacher_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Teacher --</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Subject</label>
                                            <select name="subject_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Subject --</option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }} ({{ $subject->subject_code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Class</label>
                                            <select name="class_id" class="form-control select2" required style="width: 100%;">
                                                <option value="">-- Choose Class --</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Academic Year</label>
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background: #f8fafc; border-color: var(--border-color); border-radius: 8px 0 0 8px;"><i class="fa fa-calendar"></i></span>
                                                <input type="text" name="academic_year" class="form-control" value="{{ date('Y') }}" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer" style="padding: 20px 25px; background: #fafafa; border-top: 1px solid #f1f5f9;">
                                        <button type="submit" class="btn btn-success btn-block btn-flat-custom" style="background: #10b981; border: none;">
                                            <i class="fa fa-save" style="margin-right: 5px;"></i> Save Assignment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="box-modern box-warning-top">
                                <div class="box-header-modern">
                                    <h3><i class="fa fa-id-badge text-yellow" style="margin-right: 8px;"></i> Current Teaching Loads</h3>
                                </div>
                                <div class="box-body no-padding table-responsive">
                                    <table class="table table-hover table-vcenter">
                                        <thead>
                                            <tr style="background: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                                <th style="border: none;">Teacher</th>
                                                <th style="border: none;">Subject</th>
                                                <th style="border: none;">Class Allocation</th>
                                                <th style="border: none;">Academic Session</th>
                                                <th style="border: none;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assignments as $assign)
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 700; color: #1e293b;">{{ $assign->teacher->name ?? 'N/A' }}</div>
                                                    <div style="font-size: 11px; color: #64748b;">ID: #{{ $assign->teacher->id ?? '0' }}</div>
                                                </td>
                                                <td><span style="font-weight: 600; color: #334155;">{{ $assign->subject->subject_name ?? 'N/A' }}</span></td>
                                                <td><span class="badge" style="background: #eff6ff; color: #1d4ed8; font-weight: 600; padding: 5px 10px; border-radius: 6px;">{{ $assign->schoolClass->class_name ?? 'N/A' }}</span></td>
                                                <td><i class="fa fa-clock-o text-muted" style="margin-right: 4px;"></i> {{ $assign->academic_year }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('subject-assignments.destroy', $assign->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-flat text-red" style="border-radius: 6px; background: #fef2f2; border: 1px solid #fee2e2; font-weight: 600;" onclick="return confirm('Remove assignment?')">
                                                            <i class="fa fa-times" style="margin-right: 3px;"></i> Unassign
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center" style="padding: 40px;">
                                                    <div class="text-muted">
                                                        <i class="fa fa-folder-open-o fa-3x" style="color: #cbd5e1; margin-bottom: 10px;"></i>
                                                        <p style="margin: 0; font-weight: 500;">No teaching loads assigned yet.</p>
                                                    </div>
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
    </body>
</html>