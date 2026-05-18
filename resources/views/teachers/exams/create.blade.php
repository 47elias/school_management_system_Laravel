<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Schedule Exam | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .form-control.btn-flat { border-radius: 0; }
        .box-title i { margin-right: 10px; }
        .help-block { font-size: 0.9em; }
        /* Locked Context Styling */
        .locked-context { background-color: #f4f7f9; border: 1px dashed #d2d6de; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .form-control[readonly] { background-color: #eee !important; cursor: not-allowed; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-calendar-plus-o text-blue"></i> Schedule New Exam
                    <small>Teacher Portal</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('teacher.exams.index') }}">Exams</a></li>
                    <li class="active">Schedule</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">

                        {{-- Context Banner: Shows selected term from the global switcher --}}
                        <div class="locked-context">
                            <i class="fa fa-info-circle text-blue"></i>
                            This exam will be scheduled under: <strong>{{ $selectedTerm->term_name }} ({{ $selectedTerm->academic_year }})</strong>
                        </div>

                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title text-bold">
                                    Assessment Details
                                </h3>
                            </div>

                            <form action="{{ route('teacher.exams.store') }}" method="POST">
                                @csrf
                                <div class="box-body">

                                    {{-- Term Selection (Locked to Switcher Context) --}}
                                    <div class="form-group">
                                        <label>Academic Term</label>
                                        <input type="text" class="form-control btn-flat" value="{{ $selectedTerm->term_name }}" readonly>
                                        <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">
                                    </div>

                                    <div class="form-group @error('subject_assignment_id') has-error @enderror">
                                        <label for="subject_assignment_id">Assign to Class & Subject</label>
                                        <select name="subject_assignment_id" id="subject_assignment_id" class="form-control btn-flat select2" required>
                                            <option value="">-- Select Target Group --</option>
                                            @foreach($myAssignments as $assignment)
                                                <option value="{{ $assignment->id }}" {{ old('subject_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                                    Class: {{ $assignment->schoolClass->class_name }} | Subject: {{ $assignment->subject->subject_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="help-block text-muted">Select which of your assigned classes is taking this exam.</span>
                                        @error('subject_assignment_id') <span class="help-block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group @error('exam_name') has-error @enderror">
                                        <label for="exam_name">Exam Title / Assessment Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
                                            <input type="text" name="exam_name" class="form-control btn-flat"
                                                   placeholder="e.g. End of Term Examination, Class Test 1..."
                                                   value="{{ old('exam_name') }}" required>
                                        </div>
                                        @error('exam_name') <span class="help-block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group @error('exam_date') has-error @enderror">
                                                <label for="exam_date">Scheduled Date</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                    <input type="date" name="exam_date" class="form-control btn-flat"
                                                           value="{{ old('exam_date', date('Y-m-d')) }}" required>
                                                </div>
                                                @error('exam_date') <span class="help-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group @error('max_marks') has-error @enderror">
                                                <label for="max_marks">Max Marks (Weight)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-calculator"></i></span>
                                                    <input type="number" name="max_marks" class="form-control btn-flat"
                                                           value="{{ old('max_marks', 100) }}" min="1" step="0.01" required>
                                                </div>
                                                @error('max_marks') <span class="help-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-default btn-flat">
                                        <i class="fa fa-arrow-left"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-flat pull-right">
                                        <i class="fa fa-save"></i> Save Schedule
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="callout callout-info btn-flat">
                            <h4><i class="icon fa fa-info"></i> Note</h4>
                            <p>After saving, this exam will appear in your exam list. Ensure the "Max Marks" matches your grading rubric for this term.</p>
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
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
</body>
</html>
