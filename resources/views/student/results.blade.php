@extends('layouts.student')

@section('content')
<!-- Scoped Modern UI Updates -->
<style>
    body { font-family: 'Inter', sans-serif !important; background: #f8fafc; }

    /* Modern Box Styling */
    .content .box { 
        border-radius: 12px; 
        border-top: none; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
        color: #0f172a; 
        font-size: 18px; 
    }

    /* Filter Bar */
    .content .filter-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px 25px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    /* Form Inputs */
    .content .form-control { 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        padding: 8px 15px; 
        height: auto; 
        font-size: 14px; 
        color: #334155;
    }
    .content .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    
    /* Optimized Table for 10+ Subjects */
    .content .table > tbody > tr > td { 
        vertical-align: middle !important; 
        padding: 16px 20px; 
        border-top: 1px solid #f1f5f9; 
    }
    .content .table > thead > tr > th { 
        border-bottom: 2px solid #e2e8f0; 
        color: #64748b; 
        font-weight: 700; 
        padding: 14px 20px; 
        text-transform: uppercase; 
        font-size: 12px; 
        letter-spacing: 0.05em; 
        background: #f8fafc; 
    }
    .content .table-hover > tbody > tr:hover { 
        background-color: #f8fafc; 
    }

    /* Paper Breakdown UI */
    .paper-item {
        font-size: 13px;
        color: #475569;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f1f5f9;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    .paper-item:last-child { margin-bottom: 0; }
    .paper-item strong { color: #0f172a; font-family: monospace; font-size: 14px;}

    /* Comments UI */
    .comment-box {
        font-size: 13px;
        color: #475569;
        background: #fdf8f6;
        border-left: 3px solid #f97316;
        padding: 8px 12px;
        margin-bottom: 6px;
        border-radius: 0 6px 6px 0;
    }
    .comment-box:last-child { margin-bottom: 0; }
    .comment-paper-title { font-weight: 700; color: #c2410c; font-size: 11px; text-transform: uppercase; margin-bottom: 2px; display: block; }
    .no-comment { color: #94a3b8; font-style: italic; font-size: 13px; }

    /* Typography Utilities */
    .badge-grade { font-size: 16px; font-weight: 800; padding: 6px 14px; border-radius: 8px; display: inline-block;}
    .grade-A { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .grade-B { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .grade-C { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .grade-D { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
    .grade-F { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .avg-score { font-size: 18px; font-weight: 800; color: #0f172a; font-family: monospace; }
    
    /* Stats Widget */
    .stat-widget { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 12px; padding: 25px; color: white; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2); }
    .stat-value { font-size: 36px; font-weight: 800; line-height: 1; margin-bottom: 5px; }
    .stat-label { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }
</style>

<section class="content-header" style="padding-bottom: 15px;">
    <h1 style="font-weight: 800; color: #0f172a; font-size: 28px;">
        My Exam Results
    </h1>
</section>

<section class="content">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(239,68,68,0.2);">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-ban"></i> {{ session('error') }}</h4>
        </div>
    @endif

    {{-- 1. TERM SWITCHER --}}
    <div class="filter-bar">
        <form method="GET" action="{{ url()->current() }}" class="form-inline" style="display: flex; align-items: center; width: 100%; gap: 15px;">
            <div class="form-group" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0; color: #475569;"><i class="fa fa-filter text-blue"></i> Viewing Term:</label>
                <select name="term_id" class="form-control" onchange="this.form.submit()" style="min-width: 250px; font-weight: 600;">
                    @foreach($allTerms as $t)
                        <option value="{{ $t->id }}" {{ ($activeTerm->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year ?? $t->academicYear->year_name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="row">
        {{-- 2. STUDENT OVERVIEW WIDGET --}}
        <div class="col-md-3">
            <div class="stat-widget mb-4">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Term Average</div>
                        <div class="stat-value">{{ round($average, 1) }}%</div>
                    </div>
                    <i class="fa fa-line-chart" style="font-size: 40px; opacity: 0.3;"></i>
                </div>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-size: 16px; font-weight: 600;">{{ $student->surname }}, {{ $student->name }}</div>
                    <div style="font-size: 13px; opacity: 0.9;">Grade: {{ $student->grade }} | ID: {{ $student->student_number ?? $student->student_id }}</div>
                </div>
            </div>
        </div>

        {{-- 3. RESULTS TABLE --}}
        <div class="col-md-9">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-graduation-cap text-blue" style="margin-right: 8px;"></i> Academic Performance: {{ $activeTerm->term_name }}</h3>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Subject Name</th>
                                    <th style="width: 20%;">Papers</th>
                                    <th style="width: 30%;">Teacher's Comments</th>
                                    <th style="width: 15%;" class="text-center">Final Mark</th>
                                    <th style="width: 15%;" class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($current_results as $result)
                                <tr>
                                    {{-- Column 1: Subject Name --}}
                                    <td>
                                        <strong style="color: #0f172a; font-size: 15px; text-transform: uppercase; display: block;">{{ $result->subject_name }}</strong>
                                        @if($result->papers_taken > 1)
                                            <span style="font-size: 11px; font-weight: 600; color: #64748b; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; margin-top: 4px; display: inline-block;">
                                                {{ $result->papers_taken }} Papers Assessed
                                            </span>
                                        @endif
                                    </td>
                                    
                                    {{-- Column 2: Papers Breakdown --}}
                                    <td>
                                        @foreach($result->individual as $mark)
                                            <div class="paper-item">
                                                <span><i class="fa fa-file-text-o" style="color: #94a3b8; margin-right: 4px;"></i> {{ $mark->exam->exam_name }}</span>
                                                <span><strong>{{ $mark->score }}%</strong></span>
                                            </div>
                                        @endforeach
                                    </td>

                                    {{-- Column 3: Comments --}}
                                    <td>
                                        @php $hasComments = false; @endphp
                                        @foreach($result->individual as $mark)
                                            @if(!empty($mark->teacher_comment))
                                                @php $hasComments = true; @endphp
                                                <div class="comment-box">
                                                    @if($result->papers_taken > 1)
                                                        <span class="comment-paper-title">{{ $mark->exam->exam_name }}</span>
                                                    @endif
                                                    "{{ $mark->teacher_comment }}"
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        @if(!$hasComments)
                                            <span class="no-comment">No comments provided.</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Column 4: Final Mark --}}
                                    <td class="text-center">
                                        <span class="avg-score">{{ $result->average_score }}%</span>
                                    </td>

                                    {{-- Column 5: Grade --}}
                                    <td class="text-center">
                                        @php
                                            $gradeClass = 'grade-F';
                                            if($result->grade == 'A') $gradeClass = 'grade-A';
                                            elseif($result->grade == 'B') $gradeClass = 'grade-B';
                                            elseif($result->grade == 'C') $gradeClass = 'grade-C';
                                            elseif($result->grade == 'D') $gradeClass = 'grade-D';
                                        @endphp
                                        <span class="badge-grade {{ $gradeClass }}">
                                            {{ $result->grade }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center" style="padding: 60px 20px;">
                                        <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                            <i class="fa fa-inbox" style="font-size: 35px; color: #94a3b8;"></i>
                                        </div>
                                        <h4 style="font-weight: 700; color: #475569;">No Results Published</h4>
                                        <p style="color: #94a3b8;">You currently have no exam marks recorded for this term.</p>
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
@endsection