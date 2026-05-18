<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Exams Management | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .box { border-top: 3px solid #3c8dbc; border-radius: 5px; }

        /* The "Fee Structure" style Filter Box */
        .filter-box { background: #ebf3f9; border: 1px solid #d2d6de; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .exam-card { transition: all 0.3s; }
        .exam-card:hover { background-color: #f9fbff !important; }

        .action-chip {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px; /* Slightly more professional than full pill */
            font-size: 11px;
            font-weight: 700;
            margin: 2px;
            text-transform: uppercase;
            transition: 0.2s;
        }
        .bg-entry { background-color: #28a745; color: white; }
        .bg-report { background-color: #007bff; color: white; }
        .action-chip:hover { opacity: 0.8; color: white; transform: translateY(-1px); }

        /* Locked Dropdown Styling */
        .form-control[disabled] { background-color: #eee !important; cursor: not-allowed; border: 1px dashed #bbb; }
        .text-amount { font-family: 'Monaco', monospace; font-weight: 700; color: #333; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Examination Hub
                    <small>Term Context: {{ $selectedTerm->term_name }}</small>
                </h1>
            </section>

            <section class="content">
                {{-- 1. THE MAIN SWITCHER (Top Bar) --}}
                <div class="box filter-box">
                    <div class="box-body">
                        <form method="GET" action="{{ url()->current() }}" class="form-inline">
                            <div class="form-group">
                                <label style="margin-right: 10px;"><i class="fa fa-filter"></i> Switch View to Term:</label>
                                <select name="term_id" class="form-control input-sm" onchange="this.form.submit()">
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ ($selectedTerm->id == $t->id) ? 'selected' : '' }}>
                                            {{ $t->term_name }} ({{ $t->academic_year ?? $t->academicYear->year_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if(isset($activeTerm) && $selectedTerm->id == $activeTerm->id)
                                <span class="label label-success" style="margin-left: 10px;">CURRENT ACTIVE TERM</span>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="row">
                    {{-- 2. CREATE FORM (Locked to Selected Term) --}}
                    @if(Auth::user()->role == 'admin')
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-calendar-plus-o"></i> Schedule Exam</h3>
                            </div>
                            <form action="{{ route('exams.store') }}" method="POST">
                                @csrf
                                {{-- Hidden input ensures the selected term is sent even if dropdown is disabled --}}
                                <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Academic Term (Locked)</label>
                                        <select class="form-control" disabled>
                                            <option>{{ $selectedTerm->term_name }} ({{ $selectedTerm->academic_year }})</option>
                                        </select>
                                        <small class="text-muted">Use the top switcher to change terms.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Exam Title</label>
                                        <input type="text" name="exam_name" class="form-control" placeholder="e.g. End of Term One" required>
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
                                            <div class="form-group">
                                                <label>Exam Date</label>
                                                <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label>Max Marks</label>
                                                <input type="number" name="max_marks" class="form-control" value="100" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                                        <i class="fa fa-check"></i> <b>CONFIRM SCHEDULE</b>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- 3. THE LIST (Filtered by selected term only) --}}
                    <div class="{{ Auth::user()->role == 'admin' ? 'col-md-8' : 'col-md-12' }}">
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-list"></i> Exams for {{ $selectedTerm->term_name }}</h3>
                                <div class="box-tools pull-right">
                                    <span class="badge bg-blue">{{ $exams->count() }} Exam(s)</span>
                                </div>
                            </div>
                            <div class="box-body no-padding">
                                <div class="table-responsive">
                                    <table class="table table-hover" style="vertical-align: middle;">
                                        <thead>
                                            <tr class="bg-gray">
                                                <th style="width: 15%">Date</th>
                                                <th style="width: 25%">Exam & Subject</th>
                                                <th>Mark Entry</th>
                                                <th class="text-center">Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($exams as $exam)
                                            <tr class="exam-card">
                                                <td>
                                                    <span class="text-bold text-blue">
                                                        {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}
                                                    </span><br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->diffForHumans() }}</small>
                                                </td>
                                                <td>
                                                    <span class="label label-default">{{ $exam->subject?->subject_name }}</span><br>
                                                    <strong>{{ $exam->exam_name }}</strong><br>
                                                    <small class="text-muted">MAX MARKS: <span class="text-amount">{{ $exam->max_marks ?? 100 }}</span></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group-flex">
                                                        @foreach($grades as $grade)
                                                            <a href="{{ route('marks.create', ['exam_id' => $exam->id, 'grade' => $grade]) }}"
                                                               class="action-chip bg-entry" title="Enter Marks">
                                                                {{ $grade }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if(Auth::user()->role == 'admin')
                                                    <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Delete this exam?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center" style="padding: 60px;">
                                                    <i class="fa fa-folder-open-o fa-3x text-gray"></i>
                                                    <p class="text-gray">No exams scheduled for <strong>{{ $selectedTerm->term_name }}</strong>.</p>
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

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
