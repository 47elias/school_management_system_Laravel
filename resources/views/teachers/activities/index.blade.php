<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Continuous Assessment | SIT</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')

    <style>
        .ca-icon {
            width: 60px; height: 60px; border-radius: 50%;
            background: rgba(255,255,255,0.2); border: 2px solid #fff;
            text-align: center; line-height: 56px; font-size: 24px;
            color: #fff; float: left;
        }
        .ca-info-box { margin-left: 75px; }
        .ca-count-badge { font-size: 13px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Continuous Assessment
                    <small>Daily classwork, homework & quiz marks — independent of exams</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teacher.dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Continuous Assessment</li>
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
                    @php $colors = ['bg-blue', 'bg-purple', 'bg-maroon', 'bg-teal', 'bg-orange', 'bg-aqua']; @endphp

                    @forelse($assignments as $index => $item)
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="box box-widget widget-user-2 shadow-sm">
                                <div class="widget-user-header {{ $colors[$index % count($colors)] }}">
                                    <div class="ca-icon">{{ substr($item->subject->subject_name ?? '?', 0, 1) }}</div>
                                    <div class="ca-info-box">
                                        <h3 class="widget-user-username" style="margin:0; font-size:18px;">{{ $item->subject->subject_name ?? 'Subject' }}</h3>
                                        <h5 class="widget-user-desc" style="margin:0; opacity:.9;"><i class="fa fa-university"></i> {{ $item->schoolClass->class_name ?? 'N/A' }}</h5>
                                    </div>
                                </div>

                                <div class="box-body">
                                    <span class="label label-default ca-count-badge">
                                        <i class="fa fa-list"></i> {{ $item->activities_count }} activities logged
                                    </span>
                                </div>

                                <div class="box-footer no-padding">
                                    <ul class="nav nav-stacked">
                                        <li>
                                            <a href="#" class="text-green" data-toggle="modal" data-target="#logModal"
                                               data-assignment-id="{{ $item->id }}"
                                               data-subject="{{ $item->subject->subject_name ?? '' }}"
                                               data-class="{{ $item->schoolClass->class_name ?? '' }}">
                                                <strong><i class="fa fa-plus-circle"></i> Log Today's Activity</strong>
                                                <span class="pull-right"><i class="fa fa-chevron-right"></i></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('teacher.activities.history', $item->id) }}" class="text-blue">
                                                <i class="fa fa-history"></i> View History & CA Averages
                                                <span class="pull-right"><i class="fa fa-chevron-right"></i></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <div class="callout callout-warning" style="background:#fff !important; border-left-color:#f39c12 !important;">
                                <h4 class="text-yellow"><i class="icon fa fa-info-circle"></i> No Teaching Load</h4>
                                <p>You are not currently assigned to any subject/class, so there is nothing to record activities for yet.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- QUICK LOG MODAL: title, type, date (defaults today), max score --}}
        <div class="modal fade" id="logModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form action="{{ route('teacher.activities.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-plus-circle text-green"></i> Log Class Activity</h4>
                            <small class="text-muted" id="modalContext"></small>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="subject_assignment_id" id="modalAssignmentId">

                            <div class="form-group">
                                <label>Activity Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 4 Homework, Pop Quiz, Group Practical" required>
                            </div>

                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="type" class="form-control" required>
                                            <option value="classwork">Classwork</option>
                                            <option value="homework">Homework</option>
                                            <option value="quiz">Quiz</option>
                                            <option value="participation">Participation</option>
                                            <option value="practical">Practical</option>
                                            <option value="project">Project</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="activity_date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Max Score</label>
                                        <input type="number" name="max_score" class="form-control" value="100" min="1" max="1000" required>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label>Weight <small class="text-muted">(optional)</small></label>
                                        <input type="number" step="0.1" name="weight" class="form-control" value="1.0" min="0.1" max="10">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Notes <small class="text-muted">(optional)</small></label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-arrow-right"></i> Create & Enter Scores</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script>
        $('#logModal').on('show.bs.modal', function (e) {
            var btn = $(e.relatedTarget);
            $('#modalAssignmentId').val(btn.data('assignment-id'));
            $('#modalContext').text(btn.data('subject') + ' — ' + btn.data('class'));
        });
    </script>
</body>
</html>
