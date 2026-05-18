<!DOCTYPE html>
<html>
<head>
    <title>Exam Report | {{ $exam->subject?->subject_name ?? 'Subject' }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <style>
        @media print {
            .no-print, .main-sidebar, .main-header, .main-footer { display: none !important; }
            .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
            .box { border: none !important; }
            .table-bordered > thead > tr > th,
            .table-bordered > tbody > tr > td { border: 1px solid #000 !important; }
        }
        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-logo { font-size: 28px; font-weight: bold; margin-bottom: 0; }
        .report-title { font-size: 18px; letter-spacing: 2px; margin-top: 5px; color: #555; }
        .stats-card { background: #f4f4f4; padding: 15px; border-radius: 5px; text-align: center; }
        .stats-val { display: block; font-size: 20px; font-weight: bold; color: #3c8dbc; }
        .signature-row { margin-top: 50px; padding: 20px; }
        .sig-box { border-top: 1px solid #000; text-align: center; padding-top: 5px; width: 200px; display: inline-block; margin: 0 40px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header no-print">
                <h1>
                    Class Performance
                    <small>Official Subject Report</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('exams.index') }}"><i class="fa fa-dashboard"></i> Exams</a></li>
                    <li class="active">Subject Report</li>
                </ol>
            </section>

            <section class="content">
                <div class="box box-solid">
                    <div class="box-body">
                        {{-- Official Header --}}
                        <div class="report-header">
                            <h1 class="school-logo">{{ env('SCHOOL_NAME', 'ACADEMIC INSTITUTION') }}</h1>
                            <p class="report-title">SUBJECT PERFORMANCE REPORT</p>
                            <div class="row">
                                <div class="col-xs-12">
                                    <strong>Term:</strong> {{ $exam->term?->term_name ?? 'N/A' }} |
                                    <strong>Subject:</strong> {{ $exam->subject?->subject_name }} |
                                    <strong>Exam:</strong> {{ $exam->exam_name }} |
                                    <strong>Grade:</strong> {{ $grade }} |
                                    <strong>Date:</strong> {{ date('d M, Y') }}
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row no-print" style="margin-bottom: 20px;">
                            <div class="col-xs-12 text-center">
                                <button class="btn btn-success btn-flat" onclick="window.print()">
                                    <i class="fa fa-print"></i> PRINT OFFICIAL REPORT
                                </button>
                                <a href="{{ route('exams.index') }}" class="btn btn-default btn-flat" style="margin-left: 10px;">
                                    <i class="fa fa-arrow-left"></i> BACK TO EXAMS
                                </a>
                            </div>
                        </div>

                        {{-- Statistics Summary --}}
                        <div class="row" style="margin-bottom: 30px;">
                            @php
                                $allScores = $students->map(fn($s) => $s->marks->firstWhere('exam_id', $exam->id)->score ?? 0);
                                $avg = $allScores->count() > 0 ? $allScores->avg() : 0;
                                $max = $allScores->count() > 0 ? $allScores->max() : 0;
                                $min = $allScores->count() > 0 ? $allScores->min() : 0;
                            @endphp
                            <div class="col-xs-4">
                                <div class="stats-card">
                                    <span class="text-muted small uppercase">Average Score</span>
                                    <span class="stats-val">{{ number_format($avg, 1) }}%</span>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="stats-card">
                                    <span class="text-muted small uppercase">Highest Score</span>
                                    <span class="stats-val">{{ $max }}%</span>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="stats-card">
                                    <span class="text-muted small uppercase">Lowest Score</span>
                                    <span class="stats-val">{{ $min }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ranking Table --}}
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr style="background-color: #f9f9f9 !important;">
                                    <th style="width: 60px" class="text-center">Rank</th>
                                    <th>Student Name</th>
                                    <th class="text-center" style="width: 100px;">Score (%)</th>
                                    <th class="text-center" style="width: 80px;">Grade</th>
                                    <th>Teacher's Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sortedStudents = $students->sortByDesc(function($s) use ($exam) {
                                        return $s->marks->firstWhere('exam_id', $exam->id)->score ?? -1;
                                    });
                                    $currentRank = 0;
                                    $lastScore = null;
                                    $displayRank = 0;
                                @endphp

                                @foreach($sortedStudents as $student)
                                    @php
                                        $mark = $student->marks->firstWhere('exam_id', $exam->id);
                                        $score = $mark ? $mark->score : 0;

                                        // Tie-ranking logic
                                        $currentRank++;
                                        if ($score !== $lastScore) {
                                            $displayRank = $currentRank;
                                        }
                                        $lastScore = $score;

                                        $gradeLetter = $mark ? \App\Models\Mark::getGrade($score) : 'U';

                                        $labelColor = 'default';
                                        if(in_array($gradeLetter, ['A', 'B'])) $labelColor = 'success';
                                        elseif($gradeLetter == 'C') $labelColor = 'primary';
                                        elseif(in_array($gradeLetter, ['D', 'E'])) $labelColor = 'warning';
                                        elseif($gradeLetter == 'U') $labelColor = 'danger';
                                    @endphp
                                    <tr>
                                        <td class="text-center"><b>{{ $displayRank }}</b></td>
                                        <td>{{ strtoupper($student->surname) }}, {{ $student->name }}</td>
                                        <td class="text-center font-bold">
                                            {{ $mark ? number_format($mark->score, 0) : '0' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-{{ $labelColor }}">{{ $gradeLetter }}</span>
                                        </td>
                                        <td class="small">{{ $mark->teacher_comment ?? '---' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Signature Section (Visible in Print) --}}
                        <div class="row signature-row">
                            <div class="col-xs-12 text-center">
                                <div class="sig-box">
                                    <strong>Class Teacher</strong><br>
                                    <small>Signature & Date</small>
                                </div>
                                <div class="sig-box">
                                    <strong>Head of Department</strong><br>
                                    <small>Signature & Date</small>
                                </div>
                                <div class="sig-box">
                                    <strong>School Stamp</strong><br>
                                    <small>Official Seal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>
</body>
</html>
