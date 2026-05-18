<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Manage Marks | {{ $assignment->subject->subject_name }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        /* Modern Web UI */
        .content-wrapper { background-color: #f1f5f9 !important; }
        .box { border-radius: 12px; border: none; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; }
        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        /* --- PRINT MASTER STYLING --- */
        @media print {
            @page { size: portrait; margin: 1.5cm; }

            /* Hide UI Elements */
            .main-sidebar, .main-header, .content-header, .main-footer,
            .no-print, .btn, .box-footer, .input-group-addon {
                display: none !important;
            }

            .content-wrapper, .wrapper {
                margin-left: 0 !important;
                background: white !important;
                border: none !important;
            }

            .box { border: 1px solid #000 !important; box-shadow: none !important; }

            /* Professional Document Header */
            .print-header { display: block !important; border-bottom: 2px solid #000; margin-bottom: 25px; padding-bottom: 15px; }
            .print-header h2 { margin: 0; font-weight: 800; text-transform: uppercase; }

            /* Table Formatting for Ink-Friendly Print */
            .table-bordered { border: 1px solid #000 !important; width: 100% !important; }
            .table-bordered > thead > tr > th,
            .table-bordered > tbody > tr > td {
                border: 1px solid #000 !important;
                color: #000 !important;
                padding: 8px !important;
                background-color: transparent !important;
            }

            /* Clean up Input appearance for Paper */
            .score-input {
                border: none !important;
                background: transparent !important;
                font-weight: bold !important;
                text-align: center !important;
                font-size: 14px !important;
            }
            .remarks-input { border: none !important; font-style: italic !important; }

            /* Signature Section */
            .print-signatures { display: flex !important; justify-content: space-between; margin-top: 60px; }
            .sig-box { border-top: 1.5px solid #000; width: 30%; text-align: center; padding-top: 8px; font-size: 11px; font-weight: bold; }
        }

        .print-header, .print-signatures { display: none; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">

            {{-- HIDDEN PRINT HEADER --}}
            <div class="print-header">
                <div style="float: right; text-align: right;">
                    <p><strong>Academic Year:</strong> {{ $assignment->academic_year }}</p>
                    <p><strong>Date:</strong> {{ date('d M Y') }}</p>
                </div>
                <h2>{{ config('app.name') }} - Official Marksheet</h2>
                <p>Subject: <strong>{{ $assignment->subject->subject_name }}</strong> | Class: <strong>{{ $assignment->schoolClass->class_name }}</strong></p>
                <p>Instructor: {{ Auth::user()->name }}</p>
            </div>

            <section class="content-header p-8 no-print">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Manage Student Marks</h1>
                        <p class="text-slate-500 font-medium">Class: {{ $assignment->schoolClass->class_name }} &bull; Subject: {{ $assignment->subject->subject_name }}</p>
                    </div>
                    {{-- PRIMARY PRINT BUTTON --}}
                    <button type="button" onclick="window.print()" class="bg-slate-800 hover:bg-black text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center transition-all">
                        <i class="fa fa-print mr-3"></i> PRINT MARKSHEET
                    </button>
                </div>
            </section>

            <section class="content px-8">
                {{-- QUICK STATS --}}
                <div class="row mb-6 no-print">
                    <div class="col-md-4">
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Pass Mark</span>
                            <div class="text-2xl font-black text-blue-600">{{ $assignment->subject->pass_mark }}%</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-emerald-500">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Class Size</span>
                            <div class="text-2xl font-black text-emerald-600">{{ $students->count() }} Students</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-amber-500">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Max Score</span>
                            <div class="text-2xl font-black text-amber-600">100</div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('teacher.marks.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_assignment_id" value="{{ $assignment->id }}">

                    <div class="box box-primary">
                        <div class="box-body no-padding">
                            <table class="table table-hover table-bordered m-0">
                                <thead>
                                    <tr>
                                        <th class="text-center p-4" style="width: 50px">#</th>
                                        <th class="p-4">Student Details</th>
                                        <th class="p-4 text-center" style="width: 150px">Score / 100</th>
                                        <th class="p-4 text-center no-print" style="width: 120px">Status</th>
                                        <th class="p-4">Teacher's Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $index => $student)
                                        @php
                                            $existingMark = $marks->where('student_id', $student->id)->first();
                                            $score = $existingMark ? $existingMark->score : null;
                                            $isPassed = $score >= $assignment->subject->pass_mark;
                                        @endphp
                                        <tr>
                                            <td class="text-center p-4 align-middle font-bold text-slate-400">{{ $index + 1 }}</td>
                                            <td class="p-4 align-middle">
                                                <div class="font-black text-slate-800 uppercase text-sm">{{ $student->name }} {{ $student->surname }}</div>
                                                <div class="text-xs font-mono font-bold text-blue-600">{{ $student->student_number }}</div>
                                            </td>
                                            <td class="p-4 align-middle">
                                                <input type="number"
                                                       name="marks[{{ $student->id }}][score]"
                                                       class="form-control score-input text-center font-black text-lg h-10 border-slate-300 rounded-md shadow-sm"
                                                       value="{{ $score }}" min="0" max="100"
                                                       data-passmark="{{ $assignment->subject->pass_mark }}">
                                            </td>
                                            <td class="p-4 align-middle text-center status-cell no-print">
                                                @if($score !== null)
                                                    <span class="px-3 py-1 rounded-full text-xs font-black {{ $isPassed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                        {{ $isPassed ? 'PASSED' : 'FAILED' }}
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-400">PENDING</span>
                                                @endif
                                            </td>
                                            <td class="p-4 align-middle">
                                                <input type="text" name="marks[{{ $student->id }}][remarks]"
                                                       class="form-control remarks-input bg-slate-50 border-none shadow-none text-sm italic"
                                                       value="{{ $existingMark->remarks ?? '' }}" placeholder="Enter feedback...">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center p-10 font-bold text-slate-400 uppercase tracking-widest">No Student Records Found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="box-footer p-8 bg-slate-50 no-print flex justify-between items-center">
                            {{-- SECONDARY PRINT BUTTON --}}
                            <button type="button" onclick="window.print()" class="btn btn-default font-bold px-6">
                                <i class="fa fa-print mr-2"></i> Print Layout
                            </button>

                            <div class="flex gap-4">
                                <a href="{{ route('teacher.subjects') }}" class="btn btn-link text-slate-400 font-bold">Cancel</a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-10 py-3 rounded-lg shadow-lg uppercase tracking-wider transition-all">
                                    <i class="fa fa-save mr-2"></i> Save All Marks
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- HIDDEN PRINT SIGNATURES --}}
                <div class="print-signatures">
                    <div class="sig-box">Teacher's Signature</div>
                    <div class="sig-box">Verified By (HOD)</div>
                    <div class="sig-box">Principal's Approval</div>
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
    @include('components.scripts')

    <script>
        // Real-time status update
        document.querySelectorAll('.score-input').forEach(input => {
            input.addEventListener('input', function() {
                const score = parseFloat(this.value);
                const passMark = parseFloat(this.dataset.passmark);
                const statusCell = this.closest('tr').querySelector('.status-cell');

                if (isNaN(score)) {
                    statusCell.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-400">PENDING</span>';
                } else if (score >= passMark) {
                    statusCell.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">PASSED</span>';
                } else {
                    statusCell.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700">FAILED</span>';
                }
            });
        });
    </script>
</body>
</html>
