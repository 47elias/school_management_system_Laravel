@extends('layouts.student')

@section('content')
{{-- 1. DATA PREPARATION LOGIC HANDLED BY CONTROLLER --}}

<section class="content-header no-print">
    <h1>
        Academic Performance
        <small class="text-uppercase">{{ $displayTerm->term_name }} Summary</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
        <li class="active">Performance</li>
    </ol>
</section>

<section class="content">

    {{-- PRINT HEADER (Hidden on Screen) --}}
    <div class="row visible-print-block">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa fa-university"></i> {{ env('SCHOOL_NAME', 'Academic Report') }}
                <small class="pull-right">Date: {{ date('d/m/Y') }}</small>
            </h2>
            <div class="row invoice-info">
                <div class="col-xs-6 invoice-col">
                    <strong>Student Details:</strong><br>
                    Name: {{ $student->surname }}, {{ $student->name }}<br>
                    ID: {{ $student->student_number ?? 'N/A' }}<br>
                    Grade: {{ $student->grade }}
                </div>
                <div class="col-xs-6 invoice-col text-right">
                    <strong>Report Period:</strong><br>
                    Term: {{ $displayTerm->term_name }}<br>
                    Year: {{ $displayTerm->academic_year }}
                </div>
            </div>
            <hr>
        </div>
    </div>

    {{-- IDENTITY & TERM SELECTOR --}}
    <div class="row">
        <div class="col-md-8">
            <div class="box box-widget widget-user-2 shadow-lg">
                <div class="widget-user-header bg-navy">
                    <div class="widget-user-image">
                        <img class="img-circle" src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=fff&color=001f3f&size=128" alt="User Avatar">
                    </div>
                    <h3 class="widget-user-username" style="font-weight: 700;">{{ $student->surname }}, {{ $student->name }}</h3>
                    <h5 class="widget-user-desc">
                        <span class="badge bg-orange">{{ strtoupper($displayTerm->term_name) }}</span>
                        <span class="badge bg-gray" style="margin-left:5px;">GRADE: {{ $student->grade }}</span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 no-print">
            <div class="box box-default shadow-sm">
                <div class="box-header with-border">
                    <h3 class="box-title text-bold"><i class="fa fa-filter"></i> Switch Term</h3>
                </div>
                <div class="box-body">
                    <form action="{{ request()->url() }}" method="GET" id="termSwitcherForm">
                        <select name="term_id" onchange="document.getElementById('termSwitcherForm').submit()" class="form-control input-lg shadow-sm">
                            @foreach($allTerms as $t)
                                <option value="{{ $t->id }}" {{ $t->id == $displayTerm->id ? 'selected' : '' }}>
                                    {{ $t->term_name }} ({{ $t->academic_year }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PERFORMANCE STATS --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box shadow-sm border-left-aqua">
                <span class="info-box-icon bg-aqua elevation-1"><i class="fa fa-line-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase text-muted">Term Average Score</span>
                    <span class="info-box-number" style="font-size: 28px;">{{ number_format($average, 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-solid shadow-sm">
                <div class="box-body">
                    <div class="pull-left">
                        <small class="text-bold text-uppercase text-muted" style="display:block; margin-bottom: 5px;">Grading Key</small>
                        <span class="grade-badge bg-green">A 75+</span>
                        <span class="grade-badge bg-blue">B 65+</span>
                        <span class="grade-badge bg-aqua">C 50+</span>
                        <span class="grade-badge bg-yellow">D 45+</span>
                        <span class="grade-badge bg-orange">E 40+</span>
                        <span class="grade-badge bg-red">U < 40</span>
                    </div>
                    <button onclick="window.print()" class="btn btn-default btn-sm pull-right no-print" style="margin-top: 10px;">
                        <i class="fa fa-print"></i> Download Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAILED RESULTS TABLE --}}
    <div class="box box-primary shadow-sm" style="border-radius: 8px; overflow: hidden;">
        <div class="box-header with-border bg-white" style="padding: 15px;">
            <h3 class="box-title text-bold">
                <i class="fa fa-star text-yellow" style="margin-right: 10px;"></i>
                Detailed Performance: {{ $displayTerm->term_name }}
            </h3>
        </div>
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-vcenter mb-0">
                    <thead>
                        <tr style="background: #fafafa; color: #777; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                            <th style="padding-left: 20px; width: 30%;">Subject</th>
                            <th class="text-center" style="width: 15%;">Score</th>
                            <th style="width: 20%;">Progress</th>
                            <th class="text-center" style="width: 10%;">Grade</th>
                            <th style="width: 25%;">Teacher's Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($termResults as $res)
                            @php
                                // Map the Grade letter to your existing UI color classes
                                $grade = $res->grade_letter; // Uses the accessor we added to Mark.php
                                $colorMap = [
                                    'A' => 'green',
                                    'B' => 'blue',
                                    'C' => 'aqua',
                                    'D' => 'yellow',
                                    'E' => 'orange',
                                    'U' => 'red'
                                ];
                                $c = $colorMap[$grade] ?? 'gray';
                            @endphp
                            <tr>
                                <td style="padding: 15px 20px;">
                                    <div class="subject-dot bg-{{ $c }}"></div>
                                    <div class="inline-block" style="display: inline-block; vertical-align: middle;">
                                        <span class="text-bold" style="font-size: 14px; color: #2c3e50;">{{ $res->exam->subject->subject_name ?? 'Unknown' }}</span><br>
                                        <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $res->exam->exam_name }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="score-pill border-{{ $c }}">{{ (int)$res->score }}%</span>
                                </td>
                                <td>
                                    <div class="progress progress-xs mb-0 shadow-none" style="margin-top: 8px; background: #eee; border-radius: 10px;">
                                        <div class="progress-bar progress-bar-{{ $c }}" style="width: {{ $res->score }}%"></div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="text-{{ $c }} text-bold" style="font-size: 18px;">{{ $grade }}</span>
                                </td>
                                <td style="padding-right: 20px;">
                                    @if($res->teacher_comment)
                                        <div class="comment-bubble">
                                            <i class="fa fa-quote-left text-muted" style="font-size: 10px; margin-right: 5px;"></i>
                                            {{ $res->teacher_comment }}
                                        </div>
                                    @else
                                        <span class="text-muted small italic">No comment recorded</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 80px 0;">
                                    <i class="fa fa-folder-open-o text-muted" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                                    <p class="text-muted" style="font-size: 16px;">No entries found for {{ $displayTerm->term_name }}.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="box-footer bg-gray-light visible-print-block text-center" style="margin-top: 30px; border-top: 1px solid #ddd;">
            <div class="row">
                <div class="col-xs-6">
                    <br><br>
                    <p style="border-top: 1px solid #999; width: 200px; margin: 0 auto;">Class Teacher Signature</p>
                </div>
                <div class="col-xs-6">
                    <br><br>
                    <p style="border-top: 1px solid #999; width: 200px; margin: 0 auto;">School Stamp & Date</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* UI Enhancements - Preserved exactly as requested */
    .bg-navy { background-color: #001f3f !important; color: white; }
    .shadow-lg { box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
    .shadow-sm { box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important; }
    .border-left-aqua { border-left: 4px solid #00c0ef !important; }
    .grade-badge { padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-right: 3px; display: inline-block; }
    .table-vcenter td { vertical-align: middle !important; }
    .subject-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 12px; vertical-align: middle; }
    .score-pill { padding: 5px 12px; border: 2px solid; border-radius: 50px; font-weight: 800; background: #fff; display: inline-block; min-width: 60px; font-size: 12px; }
    .comment-bubble { background: #f9f9f9; border: 1px solid #eee; padding: 8px 12px; border-radius: 6px; font-size: 12px; color: #555; line-height: 1.4; }

    /* Grade-specific colors */
    .border-green { border-color: #00a65a; color: #00a65a; }
    .border-blue { border-color: #0073b7; color: #0073b7; }
    .border-aqua { border-color: #00c0ef; color: #00c0ef; }
    .border-yellow { border-color: #f39c12; color: #f39c12; }
    .border-orange { border-color: #ff851b; color: #ff851b; }
    .border-red { border-color: #dd4b39; color: #dd4b39; }

    @media print {
        .no-print { display: none !important; }
        .content { padding: 0 !important; }
        .box { border: none !important; box-shadow: none !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
    }
</style>
@endsection
