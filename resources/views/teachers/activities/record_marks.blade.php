<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Record Scores | {{ $activity->title }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        .content-wrapper { background-color: #f4f7f6 !important; }
        .table-valign td { vertical-align: middle !important; }
        .score-input { font-weight: bold; text-align: center; font-size: 16px; border-radius: 4px; }
        .input-saved { background-color: #e8f5e9 !important; border-color: #4caf50 !important; color: #2e7d32 !important; }
        .action-bar { position: fixed; bottom: 20px; right: 30px; z-index: 999; display: flex; align-items: center; gap: 15px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-edit text-green"></i> {{ $activity->title }}
                    <small>{{ $activity->subject->subject_name ?? '' }} — {{ $activity->schoolClass->class_name ?? '' }} — {{ $activity->formatted_date }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.activities.index') }}"><i class="fa fa-dashboard"></i> Continuous Assessment</a></li>
                    <li class="active">Record Scores</li>
                </ol>
            </section>

            <section class="content">

                @if(session('success'))
                    <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box {{ $activity->type_color }}">
                            <span class="info-box-icon"><i class="fa fa-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Type</span>
                                <span class="info-box-number">{{ $activity->type_label }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Class</span>
                                <span class="info-box-number">{{ $activity->schoolClass->class_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completion</span>
                                <span class="info-box-number" id="completion-perc">0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-gray">
                            <span class="info-box-icon"><i class="fa fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Max Score</span>
                                <span class="info-box-number">{{ $activity->max_score }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Student Scores</h3>
                        <div class="box-tools">
                            <span class="label label-default">Can be re-opened & edited anytime</span>
                        </div>
                    </div>

                    <form action="{{ route('teacher.activities.marks.store') }}" method="POST" id="marksForm">
                        @csrf
                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                        <div class="box-body no-padding">
                            <table class="table table-bordered table-striped table-valign">
                                <thead>
                                    <tr class="bg-gray">
                                        <th style="width: 50px" class="text-center">#</th>
                                        <th>Student</th>
                                        <th style="width: 150px" class="text-center">Score (/ {{ $activity->max_score }})</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $index => $student)
                                        @php
                                            $markRecord = $marks->get($student->id);
                                            $score = $markRecord ? $markRecord->score : '';
                                            $comment = $markRecord ? $markRecord->comment : '';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <b style="text-transform: uppercase;">{{ $student->surname }}, {{ $student->name }}</b><br>
                                                <small class="text-primary">{{ $student->student_number }}</small>
                                            </td>
                                            <td>
                                                <input type="number" name="marks[{{ $student->id }}][score]"
                                                       class="form-control score-input {{ $score !== '' ? 'input-saved' : '' }}"
                                                       value="{{ $score }}" min="0" max="{{ $activity->max_score }}" step="0.5">
                                            </td>
                                            <td>
                                                <input type="text" name="marks[{{ $student->id }}][comment]"
                                                       class="form-control comment-input {{ $comment ? 'input-saved' : '' }}"
                                                       value="{{ $comment }}" placeholder="Optional remark...">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No students found in this class.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="action-bar">
                            <div class="hidden-xs">
                                <span class="text-muted"><i class="fa fa-info-circle"></i> Press <b>Ctrl + S</b> to save all</span>
                            </div>
                            <a href="{{ route('teacher.activities.index') }}" class="btn btn-default btn-lg btn-flat">Back</a>
                            <button type="submit" class="btn btn-success btn-lg btn-flat shadow">
                                <i class="fa fa-save"></i> <b>SAVE SCORES</b>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script>
        $(document).ready(function() {
            function calculateStats() {
                var total = $('.score-input').length;
                var filled = $('.score-input').filter(function() { return $(this).val() !== ""; }).length;
                var perc = total > 0 ? Math.round((filled / total) * 100) : 0;
                $('#completion-perc').text(perc + '%');
            }
            $('.score-input, .comment-input').on('input change', function() {
                calculateStats();
                $(this).toggleClass('input-saved', $(this).val() !== '');
            });
            $('input[type=number]').on('wheel', function(e) { $(this).blur(); });
            $(document).keydown(function(e) {
                if ((e.ctrlKey || e.metaKey) && e.which == 83) {
                    e.preventDefault();
                    $('#marksForm').submit();
                }
            });
            calculateStats();
        });
    </script>
</body>
</html>
