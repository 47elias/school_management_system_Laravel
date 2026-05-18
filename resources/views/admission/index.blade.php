<!DOCTYPE html>
<html>
<head>
    <title>Admission Management | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    @include('components.scripts')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        .table-v-align td { vertical-align: middle !important; }
        .academic-summary {
            background: #f9f9f9;
            padding: 10px;
            border-left: 4px solid #00c0ef;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .modal-header-custom { background: #3c8dbc; color: white; border-top-left-radius: 3px; border-top-right-radius: 3px; }
        .info-label { display: block; font-size: 10px; font-weight: 800; color: #777; text-uppercase: uppercase; }
        .data-value { display: block; font-size: 14px; margin-bottom: 10px; color: #333; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Admissions Registry
                    <small>Review and process student applications</small>
                </h1>
            </section>

            <section class="content">

                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-aqua">
                            <div class="inner"><h3>{{ $admissions->total() }}</h3><p>Total Apps</p></div>
                            <div class="icon"><i class="fa fa-file-text-o"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-yellow">
                            <div class="inner"><h3>{{ $pendingCount ?? $admissions->where('status', 'pending')->count() }}</h3><p>Pending Review</p></div>
                            <div class="icon"><i class="fa fa-clock-o"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-green">
                            <div class="inner"><h3>{{ $approvedCount ?? $admissions->where('status', 'approved')->count() }}</h3><p>Approved</p></div>
                            <div class="icon"><i class="fa fa-check"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-red">
                            <div class="inner"><h3>{{ $declinedCount ?? $admissions->where('status', 'declined')->count() }}</h3><p>Declined</p></div>
                            <div class="icon"><i class="fa fa-ban"></i></div>
                        </div>
                    </div>
                </div>

                <div class="box box-solid">
                    <div class="box-body">
                        <form action="{{ route('admissions.manage') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control input-lg" placeholder="Search by Student Name, ID, or Tracking ID..." value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary btn-lg btn-flat"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success"><i class="icon fa fa-check"></i> {{ session('success') }}</div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Applicant Registry</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover table-v-align">
                            <thead>
                                <tr class="bg-gray">
                                    <th style="width: 120px;">Tracking ID</th>
                                    <th>Student Name</th>
                                    <th>Grade</th>
                                    <th>Guardian Phone</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admissions as $app)
                                <tr>
                                    <td><span class="badge bg-blue">{{ $app->tracking_id }}</span></td>
                                    <td>
                                        <span class="text-bold text-uppercase">{{ $app->student_name }}</span><br>
                                        <small class="text-muted">Applied: {{ $app->created_at->format('d M, Y') }}</small>
                                    </td>
                                    <td><span class="label label-info">{{ $app->applied_grade }}</span></td>
                                    <td>{{ $app->guardian_phone }}</td>
                                    <td>
                                        @php
                                            $statusClass = ['pending' => 'warning', 'approved' => 'success', 'declined' => 'danger'][$app->status] ?? 'default';
                                        @endphp
                                        <span class="label label-{{ $statusClass }}">{{ strtoupper($app->status) }}</span>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-default btn-sm btn-flat" data-toggle="modal" data-target="#modal-{{ $app->id }}">
                                            <i class="fa fa-search-plus"></i> REVIEW
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modal-{{ $app->id }}">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('admissions.update', $app->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-header modal-header-custom">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:white">&times;</span></button>
                                                    <h4 class="modal-title">Application Review: {{ $app->student_name }}</h4>
                                                </div>
                                                <div class="modal-body" style="background: #f4f4f4;">
                                                    <div class="row">
                                                        <div class="col-md-7">
                                                            <div class="box box-widget">
                                                                <div class="box-body">
                                                                    <h4 class="page-header"><i class="fa fa-user"></i> Student & Guardian Details</h4>
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <span class="info-label">DATE OF BIRTH</span>
                                                                            <span class="data-value">{{ $app->date_of_birth ?? 'N/A' }}</span>
                                                                            <span class="info-label">IDENTITY NUMBER</span>
                                                                            <span class="data-value">{{ $app->identity_number ?? 'N/A' }}</span>
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <span class="info-label">GUARDIAN NAME</span>
                                                                            <span class="data-value">{{ $app->guardian_name }}</span>
                                                                            <span class="info-label">GUARDIAN EMAIL</span>
                                                                            <span class="data-value">{{ $app->guardian_email ?? 'N/A' }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <h4 class="page-header"><i class="fa fa-graduation-cap"></i> Academic Record</h4>
                                                                    <div class="academic-summary">
                                                                        <strong>Subjects Passed:</strong><br>
                                                                        {{ $app->subjects_passed ?? 'N/A' }}
                                                                    </div>
                                                                    <div class="well well-sm">
                                                                        <strong>Previous School:</strong> {{ $app->previous_school ?? 'Not Provided' }}
                                                                    </div>

                                                                    <h4 class="page-header"><i class="fa fa-paperclip"></i> Documents</h4>
                                                                    <div class="btn-group">
                                                                        @if($app->results_file)
                                                                            <a href="{{ asset('storage/'.$app->results_file) }}" target="_blank" class="btn btn-sm btn-default btn-flat"><i class="fa fa-file-pdf-o text-red"></i> Results</a>
                                                                        @endif
                                                                        @if($app->birth_certificate)
                                                                            <a href="{{ asset('storage/'.$app->birth_certificate) }}" target="_blank" class="btn btn-sm btn-default btn-flat"><i class="fa fa-file-image-o text-blue"></i> Birth Cert</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="box box-warning">
                                                                <div class="box-header with-border"><h3 class="box-title">Process Decision</h3></div>
                                                                <div class="box-body">
                                                                    <div class="form-group">
                                                                        <label>Admission Status</label>
                                                                        <select name="status" class="form-control">
                                                                            <option value="pending" {{ $app->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                            <option value="approved" {{ $app->status == 'approved' ? 'selected' : '' }}>Approve Admission</option>
                                                                            <option value="declined" {{ $app->status == 'declined' ? 'selected' : '' }}>Decline Application</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Admin Remarks (Feedback)</label>
                                                                        <textarea name="admin_remarks" class="form-control" rows="5" placeholder="Enter reason for decision...">{{ $app->admin_remarks }}</textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-warning btn-block btn-flat text-bold">SUBMIT DECISION</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="padding:40px;">
                                        <i class="fa fa-info-circle fa-2x text-gray"></i><br>
                                        <p class="text-gray">No records found matching your search.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($admissions->hasPages())
                        <div class="box-footer clearfix">
                            <div class="pull-right">
                                {!! $admissions->appends(request()->query())->links() !!}
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
</body>
</html>
