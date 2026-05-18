<!DOCTYPE html>
<html>
<head>
    <title>Enter Marks | {{ $exam->subject?->subject_name ?? 'Subject' }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <style>
        .input-saved { background-color: #e8f5e9 !important; border-color: #a5d6a7; }
        .table-v-align td { vertical-align: middle !important; }
        .sticky-footer {
            background: #fff;
            padding: 10px 20px;
            border-top: 2px solid #3c8dbc;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Marks Entry: <span class="text-primary">{{ $exam->subject?->subject_name }}</span>
                    <small>{{ $exam->exam_name }} — Grade: <b>{{ $grade }}</b></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('exams.index') }}"><i class="fa fa-calendar"></i> Exams</a></li>
                    <li class="active">Enter Marks</li>
                </ol>
            </section>

            <section class="content">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-edit"></i> Score Sheet — {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                                </h3>
                                <div class="box-tools pull-right">
                                    <span class="label label-info" style="font-size: 12px;">Term: {{ $exam->term?->term_name }}</span>
                                    <span class="label label-default" style="font-size: 12px; margin-left: 5px;" id="entryCount">
                                        0 / {{ count($students) }} Entered
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('marks.bulk_store') }}" method="POST" id="marksForm">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                                <div class="box-body no-padding">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-v-align table-bordered mb-0">
                                            <thead>
                                                <tr class="bg-gray">
                                                    <th style="width: 50px" class="text-center">#</th>
                                                    <th>Student Details</th>
                                                    <th style="width: 180px;" class="text-center">Score (Max 100)</th>
                                                    <th>Teacher's Comment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($students as $index => $student)
                                                    @php
                                                        // Extract existing mark if it exists
                                                        $existingMark = $student->marks->firstWhere('exam_id', $exam->id);
                                                        $score = $existingMark ? $existingMark->score : '';
                                                        $comment = $existingMark ? $existingMark->teacher_comment : '';
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="pull-left" style="margin-right: 10px;">
                                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=random&size=32" class="img-circle" alt="User Image">
                                                            </div>
                                                            <strong>{{ $student->surname }}, {{ $student->name }}</strong><br>
                                                            <small class="text-muted">{{ $student->student_number ?? $student->student_id }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="input-group">
                                                                <input type="number"
                                                                       name="marks[{{ $student->id }}][score]"
                                                                       class="form-control text-center score-input {{ $score !== '' ? 'input-saved' : '' }}"
                                                                       value="{{ $score }}"
                                                                       min="0" max="100" step="0.1"
                                                                       placeholder="0-100">
                                                                <span class="input-group-addon"><b>%</b></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   name="comments[{{ $student->id }}]"
                                                                   class="form-control comment-input {{ $comment ? 'input-saved' : '' }}"
                                                                   value="{{ $comment }}"
                                                                   placeholder="e.g. Excellent progress, keep it up">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-5">
                                                            <p class="text-muted">No students found in Grade {{ $grade }}.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="box-footer sticky-footer">
                                    <div class="pull-left">
                                        <a href="{{ route('exams.index') }}" class="btn btn-default btn-flat">
                                            <i class="fa fa-arrow-left"></i> Back to Schedule
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-success btn-lg btn-flat">
                                            <i class="fa fa-save"></i> <b>SAVE ALL MARKS</b>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    <script>
        $(document).ready(function() {
            // Function to update the counter label
            function updateCounter() {
                const total = $('.score-input').length;
                const filled = $('.score-input').filter(function() {
                    return $(this).val() !== "";
                }).length;
                $('#entryCount').text(filled + ' / ' + total + ' Entered');

                if(filled === total && total > 0) {
                    $('#entryCount').removeClass('label-default').addClass('label-success');
                } else {
                    $('#entryCount').removeClass('label-success').addClass('label-default');
                }
            }

            // Listen for input changes
            $('.score-input').on('input', function() {
                updateCounter();
                if($(this).val() !== "") {
                    $(this).addClass('input-saved');
                } else {
                    $(this).removeClass('input-saved');
                }
            });

            // Initial count
            updateCounter();
        });
    </script>
</body>
</html>
