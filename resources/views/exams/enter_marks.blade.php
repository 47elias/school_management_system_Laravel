<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Enter Marks | {{ $exam->subject?->subject_name ?? 'Subject' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content .box-title { 
            font-weight: 800 !important; 
            color: #1e293b; 
            font-size: 18px; 
            margin: 0;
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

        /* Form Inputs */
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
        .content .input-group-addon {
            border-radius: 0 8px 8px 0;
            border: 1px solid #cbd5e1;
            border-left: none;
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
        }
        
        /* Saved State Feedback */
        .content .input-saved { 
            background-color: #f0fdf4 !important; 
            border-color: #86efac !important; 
            color: #166534;
        }

        /* Buttons */
        .content .btn-success-modern { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            border: none; 
            border-radius: 8px; 
            font-weight: 700; 
            padding: 12px 25px; 
            color: #fff;
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); 
        }
        .content .btn-success-modern:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); 
            color: #fff;
        }
        .content .btn-default-modern {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 20px;
            color: #475569;
            transition: all 0.2s;
        }
        .content .btn-default-modern:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* Sticky Footer */
        .content .sticky-footer {
            background: #f8fafc;
            padding: 20px 25px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 12px 12px;
        }

        /* Badges */
        .content .badge-status { 
            padding: 6px 12px; 
            border-radius: 30px; 
            font-size: 12px; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
        }
        .content .badge-info-modern { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
        .content .badge-pending { background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; }
        .content .badge-complete { background: #f0fdf4; color: #10b981; border: 1px solid #a7f3d0; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Marks Entry: <span style="color: #3b82f6;">{{ $exam->subject?->subject_name }}</span>
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">{{ $exam->exam_name }} — Grade: <b style="color: #1e293b;">{{ $grade }}</b></small>
                </h1>
                <ol class="breadcrumb" style="background: transparent; padding: 0; margin-top: 10px; font-size: 13px;">
                    <li><a href="{{ route('exams.index') }}" style="color: #3b82f6;"><i class="fa fa-calendar"></i> Exams</a></li>
                    <li class="active" style="color: #64748b;">Enter Marks</li>
                </ol>
            </section>

            <section class="content">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(239,68,68,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-ban"></i> {{ session('error') }}</h4>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-edit text-blue" style="margin-right: 5px;"></i> Score Sheet — {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                                </h3>
                                <div>
                                    <span class="badge-status badge-info-modern">Term: {{ $exam->term?->term_name }}</span>
                                    <span class="badge-status badge-pending" style="margin-left: 8px;" id="entryCount">
                                        0 / {{ count($students) }} Entered
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('marks.bulk_store') }}" method="POST" id="marksForm">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">

                                <div class="box-body no-padding">
                                    <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                                        <table class="table table-hover mb-0">
                                            <thead style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th style="width: 60px" class="text-center">#</th>
                                                    <th>Student Details</th>
                                                    <th style="width: 200px;" class="text-center">Score (Max {{ $exam->max_marks ?? 100 }})</th>
                                                    <th>Teacher's Comment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($students as $index => $student)
                                                    @php
                                                        $existingMark = $student->marks->firstWhere('exam_id', $exam->id);
                                                        $score = $existingMark ? $existingMark->score : '';
                                                        $comment = $existingMark ? $existingMark->teacher_comment : '';
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">
                                                            <span style="color: #94a3b8; font-weight: 600; font-family: monospace;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; align-items: center;">
                                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=f1f5f9&color=475569&size=40" class="img-circle" alt="User Image" style="margin-right: 15px; border: 1px solid #e2e8f0;">
                                                                <div>
                                                                    <strong style="color: #1e293b; font-size: 15px; text-transform: uppercase;">{{ $student->surname }}, {{ $student->name }}</strong><br>
                                                                    <small style="color: #64748b; font-family: monospace; font-size: 12px;">{{ $student->student_number ?? $student->student_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="input-group" style="width: 100%;">
                                                                <input type="number"
                                                                       name="marks[{{ $student->id }}][score]"
                                                                       class="form-control text-center score-input {{ $score !== '' ? 'input-saved' : '' }}"
                                                                       style="border-right: none;"
                                                                       value="{{ $score }}"
                                                                       min="0" max="{{ $exam->max_marks ?? 100 }}" step="0.1"
                                                                       placeholder="0-{{ $exam->max_marks ?? 100 }}">
                                                                <span class="input-group-addon" style="background: transparent; border-left: 0;"><i class="fa fa-percent"></i></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   name="marks[{{ $student->id }}][comment]"
                                                                   class="form-control comment-input {{ $comment ? 'input-saved' : '' }}"
                                                                   value="{{ $comment }}"
                                                                   placeholder="e.g. Excellent progress, keep it up">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center" style="padding: 60px 20px;">
                                                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                                <i class="fa fa-users" style="font-size: 35px; color: #94a3b8;"></i>
                                                            </div>
                                                            <h4 style="font-weight: 700; color: #475569;">No Students Found</h4>
                                                            <p style="color: #94a3b8;">No students are assigned to Grade {{ $grade }}.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="sticky-footer">
                                    <a href="{{ route('exams.index') }}" class="btn btn-default-modern">
                                        <i class="fa fa-arrow-left" style="margin-right: 5px;"></i> Back to Schedule
                                    </a>
                                    <button type="submit" class="btn btn-success-modern">
                                        <i class="fa fa-save" style="margin-right: 5px;"></i> SAVE ALL MARKS
                                    </button>
                                </div>
                            </form>
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
            // Function to update the counter label
            function updateCounter() {
                const total = $('.score-input').length;
                const filled = $('.score-input').filter(function() {
                    return $(this).val() !== "";
                }).length;
                
                $('#entryCount').text(filled + ' / ' + total + ' Entered');

                if(filled === total && total > 0) {
                    $('#entryCount').removeClass('badge-pending').addClass('badge-complete');
                } else {
                    $('#entryCount').removeClass('badge-complete').addClass('badge-pending');
                }
            }

            // Listen for input changes dynamically
            $('.score-input, .comment-input').on('input', function() {
                if($(this).hasClass('score-input')) {
                    updateCounter();
                }
                
                if($(this).val() !== "") {
                    $(this).addClass('input-saved');
                } else {
                    $(this).removeClass('input-saved');
                }
            });

            // Trigger initial count on page load
            updateCounter();
        });
    </script>
</body>
</html>