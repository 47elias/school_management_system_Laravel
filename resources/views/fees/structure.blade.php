<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-gray: linear-gradient(45deg, #f8fafc, #f1f5f9);
            --grad-orange: linear-gradient(45deg, #f39c12, #e67e22);
        }

        body { font-family: 'Inter', sans-serif !important; background-color: #f0f3f7 !important; }
        
        .filter-box { background: #fff; border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; padding: 15px; }
        
        /* Bulk Apply Checkbox Grid */
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 10px; }
        .grade-checkbox-label {
            font-weight: 600; cursor: pointer; background: #f8fafc; padding: 8px 10px; 
            border-radius: 6px; border: 1px solid #cbd5e1; text-align: left; display: block;
            transition: all 0.2s; font-size: 13px; color: #475569;
        }
        .grade-checkbox-label:hover { background: #e2e8f0; }
        .grade-checkbox-label input { margin-right: 8px; }

        /* Modern Collapsible Grade Boxes */
        .grade-box {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .grade-box:hover { box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .grade-box .box-header { 
            padding: 18px 20px; 
            border-bottom: 1px solid #f1f5f9; 
            cursor: pointer;
            border-radius: 10px 10px 0 0;
        }
        .grade-box .box-title { font-weight: 700; font-size: 16px; color: #1e293b; }
        .base-fee-badge {
            background: var(--grad-blue);
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 15px;
        }

        /* Tables inside grades */
        .table-fees { margin-bottom: 0; }
        .table-fees th { background-color: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; padding: 12px; border-bottom: 2px solid #e2e8f0 !important; }
        .table-fees td { vertical-align: middle; padding: 12px; color: #334155; }
        .text-amount { font-family: 'Monaco', monospace; font-weight: 700; color: #00a65a; font-size: 14px; }
        
        /* Add Form Section */
        .add-fee-section {
            background: var(--grad-gray);
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 10px 10px;
        }
        .add-fee-section label { font-size: 12px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-radius: 6px; border: 1px solid #cbd5e1; }
        
        .student-badge { background-color: #f39c12; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .timing-badge { font-size: 10px; padding: 3px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 25px 25px 10px 25px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0;">
                        <span class="text-bold">Fee Configuration</span>
                        <small style="color: #64748b; display: block; margin-top: 5px;">Manage class structures & generate invoices</small>
                    </h1>
                </div>
                
                {{-- Charge Students Action Button --}}
                @if(isset($currentTerm))
                <div>
                    <form action="{{ route('fees.structure.process_invoices') }}" method="POST" onsubmit="return confirm('Are you sure you want to charge all students for the {{ $currentTerm->term_name }} term? This will generate invoices based on the structure defined below.');">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                        <button type="submit" class="btn btn-warning text-bold" style="background: var(--grad-orange); border: none; padding: 10px 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(230,126,34,0.3); color: #fff;">
                            <i class="fa fa-bolt" style="margin-right: 5px;"></i> CHARGE STUDENTS
                        </button>
                    </form>
                </div>
                @endif
            </section>

            <section class="content">

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; margin-bottom: 20px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- Term Filter --}}
                <div class="filter-box d-flex">
                    <form method="GET" action="{{ route('fees.structure') }}" class="form-inline">
                        <div class="form-group">
                            <label style="margin-right: 12px; color: #475569;"><i class="fa fa-calendar"></i> Target Term:</label>
                            <select name="term_id" class="form-control" style="width: 250px; font-weight: 600;" onchange="this.form.submit()">
                                @foreach($terms as $t)
                                    <option value="{{ $t->id }}" {{ (isset($currentTerm) && $currentTerm->id == $t->id) ? 'selected' : '' }}>
                                        {{ $t->term_name }} ({{ $t->academic_year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(isset($currentTerm) && $currentTerm->is_current)
                            <span class="label label-success" style="margin-left: 15px; padding: 6px 12px; border-radius: 4px;">CURRENT ACTIVE TERM</span>
                        @endif
                    </form>
                </div>

                {{-- Bulk Add Section --}}
                <div class="box box-solid filter-box" style="border-left: 4px solid #00a65a;">
                    <div class="box-header with-border" style="padding-bottom: 10px;">
                        <h3 class="box-title text-bold"><i class="fa fa-copy text-green"></i> Bulk Apply Fee Structure</h3>
                        <p class="text-muted small" style="margin-top: 5px; margin-bottom: 0;">Tick multiple classes to instantly apply the same fee to all of them.</p>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('fees.structure.bulkStore') }}" method="POST">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                            
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label style="display: flex; justify-content: space-between; align-items: center;">
                                            Select Classes / Grades
                                            <div>
                                                <button type="button" class="btn btn-xs btn-default" id="selectAllGrades">Select All</button>
                                                <button type="button" class="btn btn-xs btn-default" id="deselectAllGrades">Clear</button>
                                            </div>
                                        </label>
                                        <div class="checkbox-grid">
                                            @foreach($grades as $g)
                                                <label class="grade-checkbox-label">
                                                    <input type="checkbox" name="grades[]" value="{{ $g }}" class="grade-checkbox"> Grade {{ $g }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label>Fee Description</label>
                                                <input type="text" name="fee_name" class="form-control" placeholder="e.g., General Tuition" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-5">
                                            <div class="form-group">
                                                <label>Charge Timing</label>
                                                <select name="charge_timing" class="form-control" required>
                                                    <option value="during_term">During Term</option>
                                                    <option value="end_of_term">End of Term</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-4">
                                            <div class="form-group">
                                                <label>Amount ($)</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-success btn-block text-bold" style="border-radius: 6px; padding: 6px 0;">
                                                    <i class="fa fa-check"></i> APPLY
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Grades Accordion Loop --}}
                @foreach($grades as $grade)
                    @php
                        $gradeFees = $structures->where('grade', $grade);
                        $standardFees = $gradeFees->whereNull('student_id');
                        $specialFees = $gradeFees->whereNotNull('student_id');
                        $standardTotal = $standardFees->sum('amount');
                        
                        $gradeStudents = $students->where('grade', $grade);
                    @endphp

                    <div class="box box-default collapsed-box grade-box">
                        <div class="box-header with-border" data-widget="collapse">
                            <h3 class="box-title"><i class="fa fa-graduation-cap text-blue" style="margin-right: 8px;"></i> Grade {{ $grade }}</h3>
                            <div class="box-tools pull-right">
                                <span class="base-fee-badge text-white">
                                    Base Class Fee: ${{ number_format($standardTotal, 2) }}
                                </span>
                                <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        
                        <div class="box-body no-padding" style="display: none;">
                            <div class="row" style="margin: 0;">
                                
                                {{-- Left Column: Standard Class Fees --}}
                                <div class="col-md-6" style="padding: 0; border-right: 1px solid #f1f5f9;">
                                    <h4 style="padding: 15px; margin: 0; font-size: 14px; font-weight: 700; color: #334155; background: #fff;">
                                        <i class="fa fa-users text-green"></i> Standard Class Structure
                                    </h4>
                                    <table class="table table-fees table-striped">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-center" style="width: 60px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($standardFees as $fee)
                                            <tr>
                                                <td class="text-bold">
                                                    {{ $fee->fee_name }}
                                                    @if(isset($fee->charge_timing) && $fee->charge_timing == 'end_of_term')
                                                        <span class="label label-warning timing-badge">End of Term</span>
                                                    @else
                                                        <span class="label label-info timing-badge" style="background-color: #3b82f6;">During Term</span>
                                                    @endif
                                                </td>
                                                <td class="text-right text-amount">${{ number_format($fee->amount, 2) }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('fees.structure.destroy', $fee->id) }}" method="POST" onsubmit="return confirm('Remove this standard fee from Grade {{ $grade }}?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-xs" style="border-radius: 4px;"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="3" class="text-center text-muted" style="padding: 20px;">No standard fees configured.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Right Column: Special Student Overrides --}}
                                <div class="col-md-6" style="padding: 0;">
                                    <h4 style="padding: 15px; margin: 0; font-size: 14px; font-weight: 700; color: #334155; background: #fff;">
                                        <i class="fa fa-user text-orange"></i> Individual Student Specific Fees
                                    </h4>
                                    <table class="table table-fees table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Description</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-center" style="width: 60px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($specialFees as $fee)
                                            <tr>
                                                <td><span class="student-badge">{{ $fee->student->name }} {{ $fee->student->surname }}</span></td>
                                                <td class="text-bold" style="font-size: 12px;">
                                                    {{ $fee->fee_name }}<br>
                                                    @if(isset($fee->charge_timing) && $fee->charge_timing == 'end_of_term')
                                                        <span class="label label-warning timing-badge" style="margin-left: 0; margin-top: 4px; display: inline-block;">End of Term</span>
                                                    @else
                                                        <span class="label label-info timing-badge" style="background-color: #3b82f6; margin-left: 0; margin-top: 4px; display: inline-block;">During Term</span>
                                                    @endif
                                                </td>
                                                <td class="text-right text-amount text-orange">${{ number_format($fee->amount, 2) }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('fees.structure.destroy', $fee->id) }}" method="POST" onsubmit="return confirm('Remove this special fee?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-xs" style="border-radius: 4px;"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted" style="padding: 20px;">No individual overrides.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Bottom Row: Add New Fee Form for this specific grade --}}
                            <div class="add-fee-section">
                                <form action="{{ route('fees.structure.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                                    <input type="hidden" name="grade" value="{{ $grade }}">
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                <label>Target</label>
                                                <select name="student_id" class="form-control select2" style="width: 100%;">
                                                    <option value="">-- Entire Grade {{ $grade }} --</option>
                                                    @foreach($gradeStudents as $student)
                                                        <option value="{{ $student->id }}">{{ $student->surname }}, {{ $student->name }} ({{ $student->student_number }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-0">
                                                <label>Fee Description</label>
                                                <input type="text" name="fee_name" class="form-control" placeholder="e.g., Tuition, Bus" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-0">
                                                <label>Charge Timing</label>
                                                <select name="charge_timing" class="form-control" required>
                                                    <option value="during_term">During Term</option>
                                                    <option value="end_of_term">End of Term</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-0">
                                                <label>Amount ($)</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-0">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-primary btn-block text-bold" style="border-radius: 6px;">
                                                    <i class="fa fa-plus"></i> ADD
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                @endforeach

            </section>
        </div>
        @include('layouts.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select target...",
                allowClear: true
            });

            // Bulk Checkbox Select/Deselect All Logic
            $('#selectAllGrades').on('click', function() {
                $('.grade-checkbox').prop('checked', true);
            });
            $('#deselectAllGrades').on('click', function() {
                $('.grade-checkbox').prop('checked', false);
            });
        });
    </script>
</body>
</html>