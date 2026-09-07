<!DOCTYPE html>
<html lang="en">
<head>
    <title>Assign Subjects | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')

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
        
        /* Form Inputs & Select2 Override */
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
        
        /* Select2 Modernization */
        .select2-container--default .select2-selection--single { 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            height: 43px; 
            padding: 6px 15px; 
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; }

        /* Subject List Container */
        .content .subject-list-container {
            max-height: 350px; 
            overflow-y: auto; 
            background: #f8fafc; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px; 
            padding: 15px 20px;
        }
        .content .subject-item {
            margin-top: 8px;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .content .subject-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .content input[type="checkbox"] {
            accent-color: #3b82f6;
            width: 16px;
            height: 16px;
            margin-top: 1px;
        }
        
        /* Primary Button */
        .content .btn-success-modern { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            color: #fff;
            border: none; 
            border-radius: 8px; 
            font-weight: 700; 
            padding: 12px 20px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); 
        }
        .content .btn-success-modern:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); 
            color: #fff;
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

        /* Subject Badges */
        .content .badge-subject {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin: 3px 2px;
            transition: all 0.2s;
        }
        .content .badge-subject:hover {
            background: #dbebfe;
            border-color: #93c5fd;
        }

        /* Scrollbar styling for the subject list */
        .subject-list-container::-webkit-scrollbar { width: 6px; }
        .subject-list-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .subject-list-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .subject-list-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Class-Subject Assignment
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Manage curriculum mappings</small>
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
                    <!-- Left Column: Assignment Form -->
                    <div class="col-md-5">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-link text-green"></i> Assign Subjects to Class</h3>
                            </div>

                            <form role="form" method="POST" action="{{ route('classes.assign.store') }}">
                                @csrf
                                <div class="box-body" style="padding: 25px;">
                                    <div class="form-group" style="margin-bottom: 20px;">
                                        <label>Select Class</label>
                                        <select name="class_id" class="form-control select2" style="width: 100%;" required>
                                            <option value="">-- Choose a Class --</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }} ({{ $class->class_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>Select Subjects</label>
                                        <div class="subject-list-container">
                                            @foreach($subjects as $subject)
                                            <div class="checkbox subject-item">
                                                <label style="color: #475569; font-weight: 500; font-size: 14px; width: 100%; cursor: pointer;">
                                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}">
                                                    <strong style="color: #1e293b; margin-left: 5px; margin-right: 5px;">{{ $subject->subject_code }}</strong> 
                                                    <span style="color: #64748b;">— {{ $subject->subject_name }}</span>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <p class="help-block" style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Select all subjects that should be taught in this class for the current academic term.</p>
                                    </div>
                                </div>

                                <div class="box-footer" style="padding: 20px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                                    <button type="submit" class="btn btn-success-modern btn-block">
                                        <i class="fa fa-save" style="margin-right: 5px;"></i> SAVE ASSIGNMENTS
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Current Assignments Table -->
                    <div class="col-md-7">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-list text-blue"></i> Current Class Subjects</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Class Details</th>
                                            <th>Assigned Subjects</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                        <tr>
                                            <td>
                                                <strong style="color: #1e293b; font-size: 15px; display: block;">{{ $class->class_name }}</strong>
                                                <span class="label" style="background: #e2e8f0; color: #475569; font-size: 11px; font-family: monospace;">{{ $class->class_code }}</span>
                                            </td>
                                            <td>
                                                @if($class->subjects->count() > 0)
                                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                                        @foreach($class->subjects as $assignedSubject)
                                                            <span class="badge-subject">
                                                                {{ $assignedSubject->subject_name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span style="color: #94a3b8; font-style: italic; font-size: 13px;">
                                                        <i class="fa fa-exclamation-circle"></i> No subjects assigned
                                                    </span>
                                                @endif
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
    <script>
        $(document).ready(function() {
            // Initialize Select2 if available
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "-- Choose a Class --",
                    allowClear: true
                });
            }
        });
    </script>
</body>
</html>