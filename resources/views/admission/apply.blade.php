<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admission Portal | {{ env('SCHOOL_NAME') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        body { background-color: #ecf0f5 !important; font-family: 'Source Sans Pro', sans-serif; }
        .portal-wrapper { max-width: 1000px; margin: 0 auto; padding: 20px; }

        /* Section Transitions */
        .portal-section { display: none; }
        .portal-section.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Styling */
        .school-header { text-align: center; margin-bottom: 30px; padding: 25px; background: #fff; border-radius: 3px; border-top: 4px solid #3c8dbc; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .form-section-title { border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; margin-bottom: 20px; color: #3c8dbc; font-weight: bold; text-transform: uppercase; font-size: 13px; margin-top: 20px; }
        .required-star { color: red; }

        /* PRINT CONTROL */
        @media print {
            body { background-color: #fff !important; }
            .no-print { display: none !important; }
            #official-letter-content {
                display: block !important;
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }

        #official-letter-content { border: 1px solid #ddd; padding: 50px; background: #fff; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="hold-transition">

<div class="portal-wrapper">

    {{-- 1. BRANDING HEADER --}}
    <div class="school-header no-print">
        <img src="{{ asset('images/school_logo.png') }}" style="max-height: 90px;" onerror="this.src='https://via.placeholder.com/90?text={{ env('SCHOOL_ACRONYM') }}'">
        <h2 style="font-weight: 800; color: #333; margin-top: 15px; text-transform: uppercase;">{{ env('SCHOOL_NAME') }}</h2>
        <p class="text-muted">
            {{ env('SCHOOL_ADDRESS') }} |
            <i class="fa fa-envelope"></i> {{ env('SCHOOL_EMAIL') }} |
            <i class="fa fa-phone"></i> {{ env('SCHOOL_PHONE', 'Contact Admissions') }}
        </p>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible no-print">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> Success!</h4>
            {!! session('success') !!}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible no-print">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Validation Error!</h4>
            <ul style="margin-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 2. HUB NAVIGATION --}}
    <div id="hub-section" class="portal-section {{ (!isset($application) && !$errors->any()) ? 'active' : '' }} no-print">
        <div class="row">
            <div class="col-md-6">
                <div class="small-box bg-blue" onclick="showSection('new-app')" style="cursor:pointer; transition: transform 0.2s;">
                    <div class="inner"><h3>Apply</h3><p>New Student Enrollment</p></div>
                    <div class="icon"><i class="fa fa-user-plus"></i></div>
                    <a href="#" class="small-box-footer">Open Form <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="small-box bg-olive" onclick="showSection('track-app')" style="cursor:pointer; transition: transform 0.2s;">
                    <div class="inner"><h3>Track</h3><p>Check Status & Letters</p></div>
                    <div class="icon"><i class="fa fa-search"></i></div>
                    <a href="#" class="small-box-footer">Check Progress <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TRACKING RESULT (Synchronized with Model Accessors) --}}
    @if(isset($application))
    <div id="section-result" class="portal-section active">
        <a href="{{ route('students.apply') }}" class="btn btn-default btn-sm no-print" style="margin-bottom: 15px;">
            <i class="fa fa-arrow-left"></i> Back to Hub
        </a>

        <div class="box box-primary no-print">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-weight: bold;">Application: {{ $application->tracking_id }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4 text-center">
                        <div style="padding: 20px; border: 1px solid #eee; border-radius: 5px; background: #f9f9f9;">
                            <p class="text-muted">Application Progress</p>
                            <div class="progress progress-sm active" style="margin-bottom: 10px;">
                                <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" style="width: {{ $application->progress_percentage }}%; background-color: {{ $application->status_color }};"></div>
                            </div>
                            <span class="label" style="background-color: {{ $application->status_color }}; font-size: 14px; padding: 8px 15px;">
                                {{ strtoupper($application->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <table class="table table-bordered">
                            <tr><th width="40%">Student Name:</th><td>{{ $application->student_name }}</td></tr>
                            <tr><th>Grade Applied:</th><td>{{ $application->applied_grade }}</td></tr>
                            <tr><th>National ID / BC:</th><td>{{ $application->identity_number }}</td></tr>
                            <tr><th>Submission Date:</th><td>{{ $application->created_at->format('d M Y, h:i A') }}</td></tr>
                        </table>

                        @if($application->status == 'approved')
                            <button onclick="toggleLetter()" class="btn btn-success btn-block btn-lg shadow">
                                <i class="fa fa-file-text"></i> VIEW & PRINT ADMISSION LETTER
                            </button>
                        @elseif($application->status == 'declined' || $application->status == 'rejected')
                             <div class="callout callout-danger">
                                <h4>Application Closed</h4>
                                <p>{{ $application->admin_remarks ?? 'Please contact the school for further clarification.' }}</p>
                             </div>
                        @else
                            <div class="callout callout-info">
                                <h4>Under Review</h4>
                                <p>Our admissions team is currently processing your documents. Please check back later.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- PRINTABLE LETTER --}}
        <div id="letter-container" style="display: none;">
            <div id="official-letter-content">
                {{-- Letterhead --}}
                <div class="row" style="border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px;">
                    <div class="col-xs-8">
                        <h3 style="margin: 0; font-weight: bold;">{{ env('SCHOOL_NAME') }}</h3>
                        <p>{{ env('SCHOOL_ADDRESS') }}<br>Email: {{ env('SCHOOL_EMAIL') }}<br>Phone: {{ env('SCHOOL_PHONE') }}</p>
                    </div>
                    <div class="col-xs-4 text-right">
                        <img src="{{ asset('images/school_logo.png') }}" style="max-height: 80px;" onerror="this.src='https://via.placeholder.com/80'">
                    </div>
                </div>

                <div style="font-size: 16px; line-height: 1.8;">
                    <p class="pull-right">Date: {{ date('d M Y') }}</p>
                    <p><strong>Ref:</strong> ADM/{{ $application->tracking_id }}</p>
                    <br>
                    <p>Dear <strong>{{ $application->guardian_name }}</strong>,</p>
                    <p>Parent/Guardian of <strong>{{ $application->student_name }}</strong>,</p>

                    <h4 class="text-center" style="font-weight: bold; text-decoration: underline; margin: 30px 0;">OFFICIAL PROVISIONAL ADMISSION LETTER</h4>

                    <p>Following a review of the application submitted, we are pleased to inform you that <strong>{{ $application->student_name }}</strong> has been granted provisional admission into <strong>{{ $application->applied_grade }}</strong>.</p>

                    @if($application->admin_remarks)
                        <div style="margin: 20px 0; padding: 15px; border: 1px dashed #ccc; background: #fafafa;">
                            <strong>Admissions Note:</strong><br>
                            {{ $application->admin_remarks }}
                        </div>
                    @endif

                    <p>To finalize this enrollment, please bring the original National ID/Birth Certificate and the most recent school report to the Registrar's office within 7 working days.</p>

                    <p>We look forward to welcoming you to our institution.</p>

                    <div class="row" style="margin-top: 60px;">
                        <div class="col-xs-6 text-center">
                            <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto 5px;"></div>
                            <p><strong>College Registrar</strong></p>
                        </div>
                        <div class="col-xs-6 text-center">
                            <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto 5px;"></div>
                            <p><strong>School Seal</strong></p>
                        </div>
                    </div>
                </div>

                <div class="text-center no-print" style="margin-top: 40px;">
                    <hr>
                    <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fa fa-print"></i> Print Official Letter</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. APPLICATION FORM (Synchronized with SQL sit (10).sql) --}}
    <div id="section-new-app" class="portal-section {{ ($errors->any() && !isset($application)) ? 'active' : '' }} no-print">
        <a href="{{ route('students.apply') }}" class="btn btn-default btn-sm" style="margin-bottom: 10px;"><i class="fa fa-arrow-left"></i> Back</a>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">New Student Enrollment Form</h3></div>
            <form action="{{ route('students.apply.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="box-body">

                    <div class="form-section-title">1. Student Personal Information</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Full Name (as per Birth Cert) <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter Student Name" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>National ID / Birth Cert # <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="identity_number" value="{{ old('identity_number') }}" placeholder="ID or Birth Cert Number" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Grade Applied For <span class="required-star">*</span></label>
                            <select name="grade" class="form-control" required>
                                <option value="">-- Select Grade --</option>
                                @foreach(['Form 1', 'Form 2', 'Form 3', 'Form 4', 'Lower 6', 'Upper 6'] as $g)
                                    <option value="{{ $g }}" {{ old('grade') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Date of Birth <span class="required-star">*</span></label>
                            <input type="date" class="form-control" name="dob" value="{{ old('dob') }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Residential Address <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="Home Address" required>
                        </div>
                    </div>

                    <div class="form-section-title">2. Guardian Information</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Guardian Full Name <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="guardian_name" value="{{ old('guardian_name') }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Active Phone Number <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="e.g. +263..." required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Email (For Notifications) <span class="required-star">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-section-title">3. Academic History</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Previous School Attended <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="previous_school" value="{{ old('previous_school') }}" placeholder="Name of last school" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Results Summary <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="subjects_passed" placeholder="e.g. 7 Subjects (4As, 3Bs)" value="{{ old('subjects_passed') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Brief Academic History / Disciplinary Records (If any)</label>
                        <textarea name="academic_history" class="form-control" rows="3" placeholder="Provide any additional academic context...">{{ old('academic_history') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Attach Results (PDF/JPG/PNG) <span class="required-star">*</span></label>
                            <input type="file" name="results_file" class="form-control" required>
                            <p class="help-block">Max size 4MB. Upload clear scans.</p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Recommendation / Transfer Letter</label>
                            <input type="file" name="recommendation_letter" class="form-control">
                            <p class="help-block">Optional but recommended.</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" onclick="return confirm('Ensure all details are correct before submitting.')">
                        <i class="fa fa-send"></i> SUBMIT ENROLLMENT APPLICATION
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 5. TRACKING FORM --}}
    <div id="section-track-app" class="portal-section no-print">
        <a href="{{ route('students.apply') }}" class="btn btn-default btn-sm" style="margin-bottom: 10px;"><i class="fa fa-arrow-left"></i> Back</a>
        <div class="box box-success text-center">
            <div class="box-body" style="padding: 50px;">
                <i class="fa fa-search text-success" style="font-size: 50px; margin-bottom: 20px;"></i>
                <h3>Application Tracking</h3>
                <p class="text-muted">Enter your <b>National ID</b> or the <b>Tracking ID</b> received after submission.</p>

                <form action="{{ route('students.apply.track') }}" method="POST">
                    @csrf
                    <div style="max-width: 450px; margin: 0 auto;">
                        <input type="text" name="identity_number" class="form-control input-lg text-center" placeholder="ID Number / KPC-202X-XXXXXX" required>
                        <br>
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fa fa-check-circle"></i> CHECK STATUS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function showSection(id) {
        document.querySelectorAll('.portal-section').forEach(s => s.classList.remove('active'));
        document.getElementById('section-' + id).classList.add('active');
        document.getElementById('hub-section').classList.remove('active');
    }

    function toggleLetter() {
        const letter = document.getElementById('letter-container');
        if (letter.style.display === 'none') {
            letter.style.display = 'block';
            letter.scrollIntoView({ behavior: 'smooth' });
        } else {
            letter.style.display = 'none';
        }
    }
</script>
</body>
</html>
