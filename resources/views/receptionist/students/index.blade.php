<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Student Directory | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        .badge-id { font-family: 'Source Code Pro', monospace; background: #ebf0f5; color: #222d32; padding: 3px 8px; border-radius: 3px; border: 1px solid #d2d6de; }
        .table-modern thead th { background-color: #f9fafc; color: #333; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; border-bottom: 2px solid #eee !important; }
        .col-balance-highlight { background-color: #fff9f9; border-left: 1px solid #f4f4f4; border-right: 1px solid #f4f4f4; }
        .text-black { font-weight: 900; color: #000; }
        .text-bold { font-weight: bold; }

        /* Term Switcher Styling */
        .term-selector-container { display: inline-block; margin-left: 10px; vertical-align: middle; }
        .term-select-input {
            height: 30px; padding: 2px 10px; font-size: 13px;
            border-radius: 4px; border: 1px solid #ccc; background: #fff;
            cursor: pointer; font-weight: bold;
        }

        /* Highlighting active term in dropdown */
        .opt-current { font-weight: bold; color: #00a65a; background-color: #e8f5e9; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.receptionist_sidebar')

        <div class="content-wrapper">
            {{-- 1. PRIMARY LOGIC: Identify the Active and Displayed Terms --}}
            @php
                $activeTerm = $terms->firstWhere('is_current', true);
                $currentViewTerm = $terms->firstWhere('id', $selectedTermId) ?? $activeTerm;
            @endphp

            <section class="content-header">
                <h1>
                    Student Directory
                    <small class="text-primary" style="font-weight: 600;">
                        {{ $currentViewTerm->term_name }} ({{ $currentViewTerm->academic_year }})
                    </small>

                    {{-- Term Switcher --}}
                    <div class="term-selector-container">
                        <form action="{{ route('receptionist.students.index') }}" method="GET" id="termSwitcherForm">
                            <select name="term_id" class="term-select-input" onchange="this.form.submit()">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}"
                                        {{ $currentViewTerm->id == $term->id ? 'selected' : '' }}
                                        class="{{ $term->is_current ? 'opt-current' : '' }}">
                                        View: {{ $term->term_name }} ({{ $term->academic_year }})
                                        {{ $term->is_current ? '— [ ACTIVE ]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Maintain context --}}
                            @if($selectedGrade)<input type="hidden" name="grade" value="{{ $selectedGrade }}">@endif
                            @if($searchName)<input type="hidden" name="search" value="{{ $searchName }}">@endif
                        </form>
                    </div>

                    @if($currentViewTerm->is_current)
                        <small class="label label-success" style="font-size: 11px; vertical-align: middle; margin-left: 5px;">ACTIVE</small>
                    @endif
                </h1>
            </section>

            <section class="content">
                {{-- Filter Records Box --}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title text-bold"><i class="fa fa-search"></i> Filter Records</h3>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('receptionist.students.index') }}" method="GET">
                            <input type="hidden" name="term_id" value="{{ $currentViewTerm->id }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Grade Level</label>
                                        <select name="grade" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Grade Levels</option>
                                            @foreach($grades as $grade)
                                                <option value="{{ $grade }}" {{ $selectedGrade == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quick Search</label>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Name, Surname or ID..." value="{{ $searchName }}">
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-primary btn-flat text-bold">SEARCH</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="btn-group btn-group-justified">
                                        <a href="{{ route('receptionist.students.index') }}" class="btn btn-default btn-flat">RESET</a>
                                        <a href="{{ route('receptionist.students.create') }}" class="btn btn-success btn-flat text-bold"><i class="fa fa-plus"></i> ADD</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="box box-solid">
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-modern">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Grade</th>
                                        <th class="text-right">Total Invoiced</th>
                                        <th class="text-right">Paid to Date</th>
                                        <th class="text-right col-balance-highlight">Balance</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $index => $student)
                                        @php
                                            $invoice = (float) $student->expected_total;
                                            $paid = (float) $student->payments_sum_amount_paid;
                                            $balance = $student->calculated_balance;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $students->firstItem() + $index }}</td>
                                            <td><span class="badge-id">{{ $student->student_number }}</span></td>
                                            <td>
                                                <span class="text-bold text-uppercase">{{ $student->surname }}</span>, {{ $student->name }}
                                                @if($student->carried_forward > 0)
                                                    <i class="fa fa-exclamation-circle text-orange" title="Arrears B/F: ${{ number_format($student->carried_forward, 2) }}"></i>
                                                @endif
                                            </td>
                                            <td><span class="label label-default">{{ $student->grade }}</span></td>
                                            <td class="text-right text-muted">${{ number_format($invoice, 2) }}</td>
                                            <td class="text-right text-green text-bold">${{ number_format($paid, 2) }}</td>
                                            <td class="text-right col-balance-highlight">
                                                @if($balance <= 0)
                                                    <span class="text-green text-bold">$0.00</span>
                                                @else
                                                    <span class="text-red text-black">${{ number_format($balance, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($balance <= 0)
                                                    <span class="label label-success">CLEARED</span>
                                                @elseif(isset($student->monthly_arrears) && $student->monthly_arrears > 0)
                                                    <span class="label label-danger">OVERDUE</span>
                                                @else
                                                    <span class="label label-warning">PARTIAL</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="{{ route('receptionist.payments.create', ['student_id' => $student->id, 'term_id' => $currentViewTerm->id]) }}"
                                                       class="btn btn-xs btn-success btn-flat text-bold">
                                                        <i class="fa fa-money"></i> PAY
                                                    </a>
                                                    <a href="{{ route('receptionist.students.show', $student->id) }}?term_id={{ $currentViewTerm->id }}"
                                                       class="btn btn-xs btn-primary btn-flat text-bold">
                                                        <i class="fa fa-user"></i> STATEMENT
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center" style="padding: 40px;">
                                                No records found for the selected term.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
