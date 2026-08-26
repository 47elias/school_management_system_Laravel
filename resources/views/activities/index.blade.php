<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Continuous Assessment | Admin</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Continuous Assessment
                    <small>School-wide daily activity marks, independent of exams</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Continuous Assessment</li>
                </ol>
                <a href="{{ route('activities.analytics') }}" class="btn btn-primary" style="position:absolute; top:15px; right:15px;">
                    <i class="fa fa-bar-chart"></i> View Analytics Dashboard
                </a>
            </section>

            <section class="content">

                <div class="box box-solid box-default">
                    <div class="box-body">
                        <form method="GET" class="form-inline">
                            <div class="form-group" style="margin-right:10px;">
                                <label style="margin-right:5px;">Term</label>
                                <select name="term_id" class="form-control" onchange="this.form.submit()">
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>{{ $t->term_name }} ({{ $t->academic_year }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-right:10px;">
                                <label style="margin-right:5px;">Class</label>
                                <select name="class_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-right:10px;">
                                <label style="margin-right:5px;">Subject</label>
                                <select name="subject_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="margin-right:5px;">Type</label>
                                <select name="type" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    @foreach(['classwork','homework','quiz','participation','practical','project','other'] as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Logged Activities</h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th class="text-center">Recorded</th>
                                    <th class="text-center">Avg %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td><b>{{ $activity->title }}</b></td>
                                        <td><span class="label {{ $activity->type_color }}">{{ $activity->type_label }}</span></td>
                                        <td>{{ $activity->subjectAssignment->subject->subject_name ?? 'N/A' }}</td>
                                        <td>{{ $activity->subjectAssignment->schoolClass->class_name ?? 'N/A' }}</td>
                                        <td>{{ $activity->formatted_date }}</td>
                                        <td class="text-center">{{ $activity->marks_count }}</td>
                                        <td class="text-center">{{ $activity->average_percent }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No activities logged for this filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer">
                        {{ $activities->links() }}
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
