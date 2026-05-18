<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif !important; }
        .box { border-top: 3px solid #3c8dbc; border-radius: 5px; }
        .badge-class { background-color: #3c8dbc !important; font-size: 11px; padding: 5px 10px; }
        .badge-student { background-color: #00a65a !important; font-size: 11px; padding: 5px 10px; }
        .text-amount { font-family: 'Monaco', 'menubar', monospace; font-weight: 700; color: #333; }
        .filter-box { background: #ebf3f9; border: 1px solid #d2d6de; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Fee Configuration
                    <small>{{ $currentTerm->term_name ?? 'No Term Selected' }}</small>
                </h1>
            </section>

            <section class="content">

                <div class="box filter-box">
                    <div class="box-body">
                        <form method="GET" action="{{ route('fees.structure') }}" class="form-inline">
                            <div class="form-group">
                                <label style="margin-right: 10px;">Switch View to Term:</label>
                                <select name="term_id" class="form-control input-sm" onchange="this.form.submit()">
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ (isset($currentTerm) && $currentTerm->id == $t->id) ? 'selected' : '' }}>
                                            {{ $t->term_name }} ({{ $t->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($currentTerm && $currentTerm->is_current)
                                <span class="label label-success style="margin-left: 10px;">CURRENT ACTIVE TERM</span>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-plus"></i> Add Fee to {{ $currentTerm->term_name ?? 'Term' }}</h3>
                            </div>

                            <form action="{{ route('fees.structure.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">

                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Grade</label>
                                                <select name="grade" class="form-control" required>
                                                    <option value="">Select...</option>
                                                    @foreach($grades as $grade)
                                                        <option value="{{ $grade }}">{{ $grade }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Specific Student (Leave blank for class-wide)</label>
                                                <select name="student_id" class="form-control select2" style="width: 100%;">
                                                    <option value="">Whole Grade Class</option>
                                                    @foreach($students as $student)
                                                        <option value="{{ $student->id }}">{{ $student->surname }}, {{ $student->name }} ({{ $student->student_number }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Fee Description</label>
                                                <input type="text" name="fee_name" class="form-control" placeholder="Tuition, Transport, etc." required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Amount</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-flat pull-right">Add Fee Item</button>
                                </div>
                            </form>
                        </div>

                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    Fees for: <strong>{{ $currentTerm->term_name ?? 'Selected Term' }}</strong>
                                </h3>
                            </div>
                            <div class="box-body no-padding">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Target</th>
                                            <th>Description</th>
                                            <th>Amount Due</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($structures as $struct)
                                        <tr>
                                            <td>
                                                <span class="badge badge-class">{{ $struct->grade }}</span>
                                                @if($struct->student_id)
                                                    <span class="badge badge-student">{{ $struct->student->name }} {{ $struct->student->surname }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $struct->fee_name }}</td>
                                            <td><span class="text-amount">${{ number_format($struct->amount, 2) }}</span></td>
                                            <td class="text-center">
                                                <form action="{{ route('fees.structure.destroy', $struct->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs btn-flat" onclick="return confirm('Delete?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center" style="padding: 40px;">No fees defined for this term yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($structures->count() > 0)
                                    <tfoot>
                                        <tr style="background: #f9f9f9; font-weight: bold;">
                                            <td colspan="2" class="text-right">Term Total (Grade Weighted):</td>
                                            <td colspan="2" class="text-primary">${{ number_format($structures->sum('amount'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>
