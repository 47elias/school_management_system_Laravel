<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Record Marks | {{ $exam->subject?->subject_name }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        /* AdminLTE Enhancements */
        .content-wrapper { background-color: #f4f7f6 !important; }
        .table-valign td { vertical-align: middle !important; }

        /* Input States */
        .score-input { font-weight: bold; text-align: center; font-size: 16px; border-radius: 4px; }
        .input-saved {
            background-color: #e8f5e9 !important;
            border-color: #4caf50 !important;
            color: #2e7d32 !important;
        }

        /* Fixed Save Bar for Screen */
        .action-bar {
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Print Specifics */
        .print-only { display: none; }

        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }

            /* Expand content to full width */
            .content-wrapper { margin-left: 0 !important; background: white !important; padding: 0 !important; }
            .main-header, .main-sidebar, .main-footer { display: none !important; }

            /* Formal Table for Print */
            .table-bordered, .table-bordered>tbody>tr>td, .table-bordered>thead>tr>th {
                border: 1px solid #333 !important;
            }
            .table>thead>tr>th { background-color: #f1f1f1 !important; color: #000 !important; }

            /* Signature area */
            .print-sig-row { margin-top: 50px; display: flex; justify-content: space-between; }
            .sig-box { border-top: 1px solid #000; width: 30%; text-align: center; padding-top: 5px; font-weight: bold; font-size: 12px; }
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">

            <div class="print-only" style="padding: 20px; border-bottom: 3px double #000; margin-bottom: 20px;">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <h2 style="margin:0; text-transform: uppercase;">{{ config('app.name') }}</h2>
                            <p style="margin:0;">Official Examination Marksheet</p>
                        </td>
                        <td style="text-align: right;">
                            <p style="margin:0;"><b>Class:</b> {{ $exam->schoolClass?->class_name }}</p>
                            <p style="margin:0;"><b>Subject:</b> {{ $exam->subject?->subject_name }}</p>
                            <p style="margin:0;"><b>Date:</b> {{ date('d M Y') }}</p>
                        </td>
                    </tr>
                </table>
            </div>

            <section class="content-header no-print">
                <h1>
                    <i class="fa fa-edit text-green"></i> Marks Entry
                    <small>{{ $exam->subject?->subject_name }} — Term {{ $exam->term?->term_number }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Record Marks</li>
                </ol>
            </section>

            <section class="content">

                <div class="row no-print">
                    <div class="col-md-4">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Class Name</span>
                                <span class="info-box-number">{{ $exam->schoolClass?->class_name }}</span>
                                <div class="progress"><div id="progress-bar" class="progress-bar" style="width: 0%"></div></div>
                                <span class="progress-description"><span id="marked-count">0</span> Registered Students</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completion Status</span>
                                <span class="info-box-number" id="completion-perc">0%</span>
                                <span class="progress-description text-italic">Updated in real-time</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-gray"><i class="fa fa-print"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Print Document</span>
                                <button type="button" onclick="window.print()" class="btn btn-default btn-sm border" style="margin-top: 5px;">
                                    Generate Paper Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border no-print">
                        <h3 class="box-title">Student Evaluation List</h3>
                        <div class="box-tools">
                            <span class="label label-default">Manual Save Required</span>
                        </div>
                    </div>

                    <form action="{{ route('teacher.marks.store') }}" method="POST" id="marksForm">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                        <div class="box-body no-padding">
                            <table class="table table-bordered table-striped table-valign">
                                <thead>
                                    <tr class="bg-gray">
                                        <th style="width: 50px" class="text-center">#</th>
                                        <th>Student Identity</th>
                                        <th style="width: 150px" class="text-center">Score (%)</th>
                                        <th>Teacher Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        @php
                                            $markRecord = $marks->get($student->id);
                                            $score = $markRecord ? $markRecord->score : '';
                                            $comment = $markRecord ? $markRecord->teacher_comment : '';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <b style="text-transform: uppercase;">{{ $student->surname }}, {{ $student->name }}</b><br>
                                                <small class="text-primary">{{ $student->student_number }}</small>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="marks[{{ $student->id }}][score]"
                                                           class="form-control score-input {{ $score !== '' ? 'input-saved' : '' }}"
                                                           value="{{ $score }}" min="0" max="100">
                                                    <span class="input-group-addon no-print"><b>%</b></span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="marks[{{ $student->id }}][comment]"
                                                       class="form-control comment-input {{ $comment ? 'input-saved' : '' }}"
                                                       value="{{ $comment }}" placeholder="Add remark...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="box-footer print-only" style="border:none;">
                            <p style="font-size: 11px; margin-bottom: 40px;"><i>Declaration: I certify that the scores provided above are accurate and have been cross-checked with the student scripts.</i></p>
                            <div class="print-sig-row">
                                <div class="sig-box">Teacher's Signature</div>
                                <div class="sig-box">H.O.D. Signature</div>
                                <div class="sig-box">Principal's Signature</div>
                            </div>
                        </div>

                        <div class="action-bar no-print">
                            <div class="hidden-xs">
                                <span class="text-muted"><i class="fa fa-info-circle"></i> Press <b>Ctrl + S</b> to save all</span>
                            </div>
                            <a href="{{ route('teacher.exams.index') }}" class="btn btn-default btn-lg btn-flat">Cancel</a>
                            <button type="submit" class="btn btn-success btn-lg btn-flat shadow">
                                <i class="fa fa-save"></i> <b>SAVE ALL RECORDS</b>
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

                $('#marked-count').text(filled + ' / ' + total);
                $('#completion-perc').text(perc + '%');
                $('#progress-bar').css('width', perc + '%');
            }

            $('.score-input, .comment-input').on('input change', function() {
                calculateStats();
                if ($(this).val() !== "") {
                    $(this).addClass('input-saved');
                } else {
                    $(this).removeClass('input-saved');
                }
            });

            // Prevent wheel
            $('input[type=number]').on('wheel', function(e) { $(this).blur(); });

            // Keyboard Save
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
