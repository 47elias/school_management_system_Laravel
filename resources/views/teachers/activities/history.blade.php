<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CA History | {{ $assignment->subject->subject_name ?? '' }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    CA History
                    <small>{{ $assignment->subject->subject_name ?? '' }} — {{ $assignment->schoolClass->class_name ?? '' }}{{ $term ? ' — '.$term->term_name : '' }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.activities.index') }}"><i class="fa fa-dashboard"></i> Continuous Assessment</a></li>
                    <li class="active">History</li>
                </ol>
            </section>

            <section class="content">

                @if(session('success'))
                    <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-7">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-list"></i> Logged Activities</h3>
                            </div>
                            <div class="box-body no-padding">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>Title</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th class="text-center">Recorded</th>
                                            <th class="text-center">Avg %</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activities as $activity)
                                            <tr>
                                                <td><b>{{ $activity->title }}</b>{!! $activity->notes ? '<br><small class="text-muted">'.e($activity->notes).'</small>' : '' !!}</td>
                                                <td><span class="label {{ $activity->type_color }}">{{ $activity->type_label }}</span></td>
                                                <td>{{ $activity->formatted_date }}</td>
                                                <td class="text-center">{{ $activity->marks_count }} / {{ $students->count() }}</td>
                                                <td class="text-center">{{ $activity->average_percent }}%</td>
                                                <td class="text-center">
                                                    <a href="{{ route('teacher.activities.record', $activity->id) }}" class="btn btn-xs btn-primary" title="Edit scores"><i class="fa fa-pencil"></i></a>
                                                    <form action="{{ route('teacher.activities.destroy', $activity->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this activity and all its scores?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted">No activities logged yet this term.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-line-chart"></i> Student CA Averages{{ $term ? ' ('.$term->term_name.')' : '' }}</h3>
                            </div>
                            <div class="box-body no-padding">
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>Student</th>
                                            <th class="text-center">CA Average</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $student)
                                            <tr>
                                                <td>{{ $student->surname }}, {{ $student->name }}</td>
                                                <td class="text-center">
                                                    <span class="label {{ $student->ca_average >= 50 ? 'label-success' : 'label-danger' }}">
                                                        {{ $student->ca_average }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted">No students in this class.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
