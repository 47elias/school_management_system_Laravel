<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Exam Schedule | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .table-vcenter td { vertical-align: middle !important; }
        .exam-date-box {
            background: #f4f4f4;
            border-left: 3px solid #3c8dbc;
            padding: 5px 10px;
            display: inline-block;
            font-weight: 600;
            color: #333;
        }
        .subject-title {
            font-size: 14px;
            font-weight: 700;
            display: block;
            color: #333;
        }
        .subject-code {
            font-family: monospace;
            color: #777;
        }
        .empty-state {
            padding: 60px 0;
            color: #999;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 15px;
            display: block;
        }
        /* Term Switcher Alert styling */
        .context-banner {
            background: #fff;
            padding: 10px 15px;
            border-bottom: 1px solid #d2d6de;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-file-text-o text-blue"></i> My Exam Schedule
                    <small>Assessment Planning & Grading</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Exams</li>
                </ol>
            </section>

            <section class="content">
                {{-- Context Banner to show which Term is currently selected --}}
                <div class="context-banner btn-flat">
                    <span><i class="fa fa-filter text-blue"></i> Showing exams for: <strong>{{ $selectedTerm->term_name }} ({{ $selectedTerm->academic_year }})</strong></span>
                    @if($selectedTerm->is_current)
                        <span class="label label-success">ACTIVE TERM</span>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible btn-flat">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Scheduled Assessments</h3>
                                <div class="box-tools pull-right">
                                    <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary btn-sm btn-flat">
                                        <i class="fa fa-plus"></i> Schedule New Exam
                                    </a>
                                </div>
                            </div>

                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover table-striped table-vcenter">
                                    <thead>
                                        <tr>
                                            <th style="width: 150px">Date</th>
                                            <th>Subject</th>
                                            <th>Class</th>
                                            <th>Exam Details</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-right" style="padding-right: 20px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($exams as $exam)
                                        <tr>
                                            <td>
                                                <div class="exam-date-box">
                                                    {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="subject-title">{{ $exam->subject->subject_name ?? 'N/A' }}</span>
                                                <small class="subject-code">{{ $exam->subject->subject_code ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="label label-info btn-flat" style="font-size: 11px">
                                                    {{ $exam->schoolClass->class_name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $exam->exam_name }}</strong>
                                                <div class="text-muted small">Max Marks: {{ $exam->max_marks ?? '100' }}</div>
                                            </td>
                                            <td class="text-center">
                                                @if($exam->marks_count > 0)
                                                    <span class="label label-success btn-flat">GRADED ({{ $exam->marks_count }})</span>
                                                @else
                                                    <span class="label label-warning btn-flat">NO MARKS YET</span>
                                                @endif
                                            </td>
                                            <td class="text-right" style="padding-right: 20px">
                                                <div class="btn-group">
                                                    <a href="{{ route('teacher.marks.manage', $exam->id) }}"
                                                       class="btn btn-sm btn-info btn-flat">
                                                        <i class="fa fa-pencil"></i> Enter Marks
                                                    </a>

                                                    <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-default btn-flat"
                                                                onclick="return confirm('Delete this exam and all associated marks? This cannot be undone.')"
                                                                title="Delete">
                                                            <i class="fa fa-trash text-red"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center empty-state">
                                                <i class="fa fa-calendar-times-o"></i>
                                                <p>No scheduled exams found for this term.</p>
                                                <a href="{{ route('teacher.exams.create') }}" class="btn btn-sm btn-primary btn-flat">
                                                    Create your first exam
                                                </a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="box-footer">
                                <div class="callout callout-info btn-flat" style="margin-bottom: 0;">
                                    <h4><i class="fa fa-info"></i> Teacher Portal Info</h4>
                                    <p>The "Enter Marks" section allows you to record scores for students. Ensure all marks are recorded before the term ends. For student list discrepancies, contact the Registrar.</p>
                                </div>
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
