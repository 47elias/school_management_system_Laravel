<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admission Management | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    @include('components.scripts')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- FINAL VERSION: Modernized Admin Styles -->
    <style>
        body { background: #f0f4f8 !important; font-family: 'Source Sans Pro', sans-serif; }
        .content-wrapper { background-color: transparent !important; }
        
        /* Modern Gradient Cards */
        .info-box-modern { border-radius: 12px; padding: 25px 20px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); transition: transform 0.3s ease; margin-bottom: 20px; }
        .info-box-modern:hover { transform: translateY(-5px); }
        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-gradient-red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .info-box-modern .inner h3 { font-size: 36px; font-weight: 800; margin: 0 0 5px 0; letter-spacing: 1px; }
        .info-box-modern .inner p { font-size: 15px; margin: 0; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-box-modern .icon { position: absolute; right: 20px; top: 20px; font-size: 55px; opacity: 0.2; }

        /* Box & Table Overrides */
        .box { border-radius: 12px; border-top: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .box-header { border-bottom: 1px solid #f1f5f9; padding: 20px; background: #fff; border-radius: 12px 12px 0 0; }
        .box-title { font-weight: 700 !important; color: #1e293b; font-size: 18px; }
        
        .table > tbody > tr > td { vertical-align: middle !important; padding: 15px; border-top: 1px solid #f1f5f9; }
        .table > thead > tr > th { border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; padding: 15px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; background: #f8fafc; }
        
        .status-badge { padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        
        /* Modal Modernization */
        .modal-content { border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .modal-header-custom { background: #f8fafc; color: #1e293b; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 25px; }
        .modal-title { font-weight: 800; font-size: 20px; }
        .modal-body { padding: 30px; background: #fff; }
        
        /* Data Display Typography */
        .section-title { font-size: 14px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 20px; margin-top: 15px; }
        .section-title:first-child { margin-top: 0; }
        .info-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
        .data-value { font-size: 15px; color: #334155; font-weight: 600; margin-bottom: 20px; display: block; word-wrap: break-word; }
        .data-value.block-text { background: #f8fafc; border-left: 4px solid #cbd5e1; padding: 12px 15px; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 400; }
        
        /* Forms inside modal */
        .form-control { border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: none; padding: 10px 15px; height: auto; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        /* Document Buttons */
        .doc-btn { border-radius: 6px; padding: 10px 15px; font-weight: 600; transition: all 0.2s; margin-bottom: 10px; display: block; text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; }
        .doc-btn:hover { background: #f1f5f9; color: #3b82f6; border-color: #cbd5e1; text-decoration: none; }
        .doc-btn i { font-size: 18px; width: 25px; text-align: center; margin-right: 5px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Admissions Registry
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Review and process student applications</small>
                </h1>
            </section>

            <section class="content">

                <!-- 1. MODERN DASHBOARD CARDS -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-blue">
                            <div class="inner"><h3>{{ $admissions->total() }}</h3><p>Total Apps</p></div>
                            <div class="icon"><i class="fa fa-folder-open"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-orange">
                            <div class="inner"><h3>{{ $pendingCount ?? $admissions->where('status', 'pending')->count() }}</h3><p>Pending Review</p></div>
                            <div class="icon"><i class="fa fa-hourglass-half"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-green">
                            <div class="inner"><h3>{{ $approvedCount ?? $admissions->where('status', 'approved')->count() }}</h3><p>Approved</p></div>
                            <div class="icon"><i class="fa fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box-modern bg-gradient-red">
                            <div class="inner"><h3>{{ $declinedCount ?? $admissions->where('status', 'declined')->count() }}</h3><p>Declined / Rejected</p></div>
                            <div class="icon"><i class="fa fa-times-circle"></i></div>
                        </div>
                    </div>
                </div>

                <!-- 2. SEARCH BAR -->
                <div class="box">
                    <div class="box-body" style="padding: 20px;">
                        <form action="{{ route('admissions.manage') }}" method="GET">
                            <div class="input-group input-group-lg" style="box-shadow: 0 2px 10px rgba(0,0,0,0.02); border-radius: 8px;">
                                <input type="text" name="search" class="form-control" style="border-radius: 8px 0 0 8px; border-right: none;" placeholder="Search by Student Name, ID Number, or Tracking ID..." value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary" style="border-radius: 0 8px 8px 0; padding: 10px 25px;"><i class="fa fa-search"></i> SEARCH</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif

                <!-- 3. DATA TABLE -->
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list-ul text-blue"></i> Applicant Registry</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Tracking ID</th>
                                    <th>Student Details</th>
                                    <th>Grade</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admissions as $app)
                                <tr>
                                    <td>
                                        <span class="label" style="background: #e2e8f0; color: #475569; font-family: monospace; font-size: 13px; padding: 6px 10px;">{{ $app->tracking_id }}</span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: #1e293b; font-size: 15px;">{{ strtoupper($app->student_name) }}</span><br>
                                        <small style="color: #64748b; font-weight: 600;"><i class="fa fa-id-card-o"></i> {{ $app->identity_number }}</small>
                                    </td>
                                    <td><span class="label" style="background: #3b82f6; font-size: 12px; padding: 5px 10px;">{{ $app->applied_grade }}</span></td>
                                    <td>
                                        <span style="font-weight: 600; color: #334155;">{{ $app->guardian_name }}</span><br>
                                        <small style="color: #64748b;"><i class="fa fa-phone"></i> {{ $app->guardian_phone }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $bg = match($app->status) {
                                                'approved' => '#10b981',
                                                'declined', 'rejected' => '#ef4444',
                                                default => '#f59e0b',
                                            };
                                        @endphp
                                        <span class="status-badge" style="background-color: {{ $bg }}; color: #fff;">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.05);" data-toggle="modal" data-target="#modal-{{ $app->id }}">
                                            <i class="fa fa-search-plus text-blue"></i> REVIEW
                                        </button>
                                    </td>
                                </tr>

                                <!-- 4. REVIEW MODAL (Updated to show ALL fields) -->
                                <div class="modal fade" id="modal-{{ $app->id }}">
                                    <div class="modal-dialog modal-lg" style="width: 85%; max-width: 1000px;">
                                        <div class="modal-content">
                                            <form action="{{ route('admissions.update', $app->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                
                                                <div class="modal-header modal-header-custom">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 1;"><span aria-hidden="true" style="font-size: 28px; color: #94a3b8;">&times;</span></button>
                                                    <h4 class="modal-title"><i class="fa fa-edit text-blue"></i> Application Review</h4>
                                                    <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Tracking ID: <strong>{{ $app->tracking_id }}</strong> | Submitted: {{ $app->created_at->format('d M Y, h:i A') }}</p>
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <!-- LEFT COLUMN: ALL DATA -->
                                                        <div class="col-md-8" style="padding-right: 30px;">
                                                            
                                                            <div class="section-title"><i class="fa fa-user"></i> 1. Student Personal Information</div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Full Name</span>
                                                                    <span class="data-value" style="text-transform: uppercase;">{{ $app->student_name }}</span>
                                                                    
                                                                    <span class="info-label">National ID / Birth Cert</span>
                                                                    <span class="data-value">{{ $app->identity_number }}</span>
                                                                    
                                                                    <span class="info-label">Applied Grade</span>
                                                                    <span class="data-value"><span class="label" style="background: #3b82f6;">{{ $app->applied_grade }}</span></span>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Date of Birth</span>
                                                                    <span class="data-value">{{ \Carbon\Carbon::parse($app->date_of_birth)->format('d F Y') }} ({{ \Carbon\Carbon::parse($app->date_of_birth)->age }} yrs)</span>
                                                                    
                                                                    <span class="info-label">Residential Address</span>
                                                                    <span class="data-value">{{ $app->address ?? 'Not Provided' }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="section-title"><i class="fa fa-users"></i> 2. Guardian Information</div>
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <span class="info-label">Guardian Name</span>
                                                                    <span class="data-value">{{ $app->guardian_name }}</span>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <span class="info-label">Phone Number</span>
                                                                    <span class="data-value">{{ $app->guardian_phone }}</span>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <span class="info-label">Email Address</span>
                                                                    <span class="data-value">{{ $app->guardian_email ?? 'Not Provided' }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="section-title"><i class="fa fa-graduation-cap"></i> 3. Academic & Disciplinary History</div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Previous School Attended</span>
                                                                    <span class="data-value">{{ $app->previous_school ?? 'Not Provided' }}</span>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Subjects Passed / Results Summary</span>
                                                                    <span class="data-value">{{ $app->subjects_passed ?? 'Not Provided' }}</span>
                                                                </div>
                                                            </div>
                                                            @if($app->academic_history)
                                                            <span class="info-label">Academic / Disciplinary Context</span>
                                                            <div class="data-value block-text">{{ $app->academic_history }}</div>
                                                            @endif

                                                            <div class="section-title"><i class="fa fa-paperclip"></i> 4. Uploaded Documents</div>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Results / Report Book</span>
                                                                    @if($app->results_file)
                                                                        <!-- DIRECT URL ASSET LINK -->
                                                                        <a href="{{ asset($app->results_file) }}" target="_blank" class="doc-btn">
                                                                            <i class="fa fa-file-pdf-o text-red"></i> View Results Document
                                                                        </a>
                                                                    @else
                                                                        <span class="data-value text-muted">No Results Uploaded</span>
                                                                    @endif
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="info-label">Recommendation Letter</span>
                                                                    @if($app->recommendation_letter)
                                                                        <!-- DIRECT URL ASSET LINK -->
                                                                        <a href="{{ asset($app->recommendation_letter) }}" target="_blank" class="doc-btn">
                                                                            <i class="fa fa-file-text-o text-blue"></i> View Recommendation Letter
                                                                        </a>
                                                                    @else
                                                                        <span class="data-value text-muted">No Recommendation Letter Provided</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- RIGHT COLUMN: DECISION FORM -->
                                                        <div class="col-md-4" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                            <h3 style="font-weight: 800; color: #1e293b; margin-top: 0; margin-bottom: 20px; font-size: 20px;"><i class="fa fa-gavel text-orange"></i> Admissions Decision</h3>
                                                            
                                                            <div class="form-group" style="margin-bottom: 20px;">
                                                                <label style="color: #475569; font-weight: 700;">Update Application Status</label>
                                                                <select name="status" class="form-control" style="height: 45px; font-weight: 600; font-size: 15px;">
                                                                    <option value="pending" {{ $app->status == 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                                                                    <option value="approved" {{ $app->status == 'approved' ? 'selected' : '' }}>✅ Approve Admission</option>
                                                                    <option value="declined" {{ $app->status == 'declined' ? 'selected' : '' }}>❌ Decline / Reject Application</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group" style="margin-bottom: 25px;">
                                                                <label style="color: #475569; font-weight: 700;">Admin Remarks / Feedback</label>
                                                                <p style="font-size: 12px; color: #94a3b8; margin-bottom: 8px;">This will be visible to the parent on their tracking portal and official letter.</p>
                                                                <textarea name="admin_remarks" class="form-control" rows="6" placeholder="Enter reason for decision or instructions for enrollment...">{{ $app->admin_remarks }}</textarea>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary btn-block btn-lg" style="border-radius: 8px; font-weight: 800; box-shadow: 0 4px 15px rgba(59,130,246,0.3); padding: 15px;">
                                                                <i class="fa fa-save"></i> SAVE DECISION
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 60px 20px;">
                                        <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                            <i class="fa fa-inbox" style="font-size: 35px; color: #94a3b8;"></i>
                                        </div>
                                        <h4 style="font-weight: 700; color: #475569;">No Applications Found</h4>
                                        <p style="color: #94a3b8;">There are no records matching your current search or filters.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($admissions->hasPages())
                        <div class="box-footer" style="background: #fff; border-radius: 0 0 12px 12px; border-top: 1px solid #f1f5f9;">
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