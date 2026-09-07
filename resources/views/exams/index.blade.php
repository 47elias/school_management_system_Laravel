<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Exams Management | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scoped Modern UI Updates -->
    <style>
        body { font-family: 'Inter', sans-serif !important; }

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
        
        /* Filter Bar */
        .content .filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
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
            font-size: 14px; 
            transition: all 0.3s ease; 
        }
        .content .form-control:focus { 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
        }
        .content .form-control[disabled] { 
            background-color: #f1f5f9 !important; 
            color: #94a3b8;
            border: 1px dashed #cbd5e1; 
            cursor: not-allowed; 
        }

        /* Select2 Modernization */
        .select2-container--default .select2-selection--single { 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            height: 43px; 
            padding: 6px 15px; 
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 41px; }
        
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
            font-size: 14px; 
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

        /* Subject Card Style */
        .subject-row { cursor: pointer; transition: all 0.2s; }
        .subject-row:hover { background: #eff6ff !important; }
        .subject-icon { width: 40px; height: 40px; border-radius: 8px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-right: 15px; }

        /* Action Chips */
        .content .action-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-decoration: none !important;
            margin: 2px;
        }
        .content .action-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .content .chip-grade {
            background: #f0fdf4;
            color: #10b981;
            border: 1px solid #a7f3d0;
        }
        .content .chip-grade:hover {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
        }
        .content .chip-scan {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        /* Modals */
        .modal-content { border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .modal-header { border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-radius: 12px 12px 0 0; padding: 20px 25px; }
        .modal-title { font-weight: 800; color: #1e293b; }
        
        /* Typography Utilities */
        .content .text-amount { font-family: monospace; font-weight: 700; color: #1e293b; font-size: 14px; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
        .content .badge-status { background: #10b981; color: white; padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Examination Hub
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Term Context: {{ $selectedTerm->term_name }}</small>
                </h1>
            </section>

            <section class="content">
                {{-- 1. THE MAIN SWITCHER (Top Bar) --}}
                <div class="filter-bar">
                    <form method="GET" action="{{ url()->current() }}" class="form-inline" style="display: flex; align-items: center; width: 100%; gap: 15px;">
                        <div class="form-group" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                            <label style="margin: 0; color: #475569;"><i class="fa fa-filter text-blue"></i> Switch View to Term:</label>
                            <select name="term_id" class="form-control" onchange="this.form.submit()" style="min-width: 250px; font-weight: 600;">
                                @foreach($terms as $t)
                                    <option value="{{ $t->id }}" {{ ($selectedTerm->id == $t->id) ? 'selected' : '' }}>
                                        {{ $t->term_name }} ({{ $t->academic_year ?? $t->academicYear->year_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(isset($activeTerm) && $selectedTerm->id == $activeTerm->id)
                            <span class="badge-status"><i class="fa fa-check-circle" style="margin-right: 4px;"></i> CURRENT ACTIVE TERM</span>
                        @endif
                    </form>
                </div>

                <div class="row">
                    {{-- 2. CREATE FORM --}}
                    @if(Auth::user()->role == 'admin')
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-calendar-plus-o text-blue"></i> Schedule Exam</h3>
                            </div>
                            <form action="{{ route('exams.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">

                                <div class="box-body" style="padding: 25px;">
                                    <div class="form-group">
                                        <label>Academic Term (Locked)</label>
                                        <select class="form-control" disabled>
                                            <option>{{ $selectedTerm->term_name }} ({{ $selectedTerm->academic_year }})</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Exam / Paper Title</label>
                                        <input type="text" name="exam_name" class="form-control" placeholder="e.g. Paper 1" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="subject_id" class="form-control select2" required style="width: 100%;">
                                            <option value="">Choose Subject...</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Exam Date</label>
                                                <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Max Marks</label>
                                                <input type="number" name="max_marks" class="form-control" value="100" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer" style="padding: 20px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-check" style="margin-right: 5px;"></i> CONFIRM SCHEDULE
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- 3. THE LIST (Subjects List Triggering Modals) --}}
                    <div class="{{ Auth::user()->role == 'admin' ? 'col-md-8' : 'col-md-12' }}">
                        <div class="box">
                            <div class="box-header with-border" style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 class="box-title"><i class="fa fa-book text-green"></i> Scheduled Subjects</h3>
                                <span class="label" style="background: #e2e8f0; color: #475569; font-size: 13px; padding: 6px 12px; border-radius: 30px;">
                                    {{ $exams->groupBy('subject_id')->count() }} Subjects Active
                                </span>
                            </div>
                            <div class="box-body no-padding">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subject Name</th>
                                                <th>Papers Scheduled</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($exams->groupBy('subject_id') as $subjectId => $subjectExams)
                                                @php $subjectName = $subjectExams->first()->subject->subject_name ?? 'Unknown Subject'; @endphp
                                                <tr class="subject-row" data-toggle="modal" data-target="#modal-subject-{{ $subjectId }}">
                                                    <td>
                                                        <div style="display: flex; align-items: center;">
                                                            <div class="subject-icon">
                                                                <i class="fa fa-graduation-cap"></i>
                                                            </div>
                                                            <div>
                                                                <strong style="color: #1e293b; font-size: 16px;">{{ $subjectName }}</strong>
                                                                <span style="display: block; color: #64748b; font-size: 12px; margin-top: 2px;">Click to view papers & classes</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span style="background: #e2e8f0; color: #334155; font-weight: 700; padding: 5px 12px; border-radius: 20px; font-size: 13px;">
                                                            {{ $subjectExams->count() }} Paper(s)
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <button class="btn btn-sm" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #3b82f6; border-radius: 6px; font-weight: 600;">
                                                            View Classes & Papers <i class="fa fa-arrow-right" style="margin-left: 5px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center" style="padding: 60px 20px;">
                                                    <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                        <i class="fa fa-folder-open-o" style="font-size: 35px; color: #94a3b8;"></i>
                                                    </div>
                                                    <h4 style="font-weight: 700; color: #475569;">No Exams Scheduled</h4>
                                                    <p style="color: #94a3b8;">Use the form to schedule an exam or paper for this term.</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>

    {{-- MODALS FOR EACH SUBJECT --}}
    @foreach($exams->groupBy('subject_id') as $subjectId => $subjectExams)
        @php $subjectName = $subjectExams->first()->subject->subject_name ?? 'Unknown Subject'; @endphp
        <div class="modal fade" id="modal-subject-{{ $subjectId }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1000px;">
                <div class="modal-content">
                    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 class="modal-title" style="font-size: 22px;">
                                <i class="fa fa-book text-blue" style="margin-right: 8px;"></i> {{ $subjectName }}
                            </h4>
                            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">Manage classes and enter marks for all scheduled papers.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 28px; opacity: 0.5;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body no-padding" style="background: #f8fafc;">
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">Paper Title / Date</th>
                                        <th style="width: 15%">Max Marks</th>
                                        <th>Select Class to Enter Marks</th>
                                        <th class="text-center" style="width: 10%">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectExams as $exam)
                                    <tr style="background: #ffffff;">
                                        <td>
                                            <strong style="color: #1e293b; font-size: 15px; display: block;">{{ $exam->exam_name }}</strong>
                                            <span style="color: #64748b; font-size: 13px;"><i class="fa fa-calendar" style="margin-right: 4px;"></i> {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-amount">{{ $exam->max_marks ?? 100 }}</span>
                                        </td>
                                        <td>
                                            {{-- Classes / Grades Badges for this specific paper --}}
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <a href="{{ route('exams.verify', $exam->id) }}" class="action-chip chip-scan" title="Scan Student QR/Barcode">
                                                    <i class="fa fa-qrcode" style="margin-right: 4px;"></i> Scan
                                                </a>
                                                
                                                @foreach($grades as $grade)
                                                    <a href="{{ route('marks.create', ['exam_id' => $exam->id, 'grade' => $grade]) }}" class="action-chip chip-grade" title="Enter Marks for Class: {{ $grade }}">
                                                        <i class="fa fa-users" style="margin-right: 4px;"></i> {{ $grade }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if(Auth::user()->role == 'admin')
                                            <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this specific exam paper? All associated marks will be lost.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #ef4444; border-radius: 6px; border: 1px solid #fecaca; width: 100%;" title="Delete Exam">
                                                    <i class="fa fa-trash" style="margin-right: 4px;"></i> Delete
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #f1f5f9; background: #fff; border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Close View</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    
    @include('components.scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Choose Subject..."
                });
            }
        });
    </script>
</body>
</html>