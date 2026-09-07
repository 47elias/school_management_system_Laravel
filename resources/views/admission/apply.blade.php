<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admission Portal | {{ env('SCHOOL_NAME') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')

    <style>
        body { 
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%) !important; 
            font-family: 'Source Sans Pro', sans-serif; 
            min-height: 100vh;
        }
        .portal-wrapper { max-width: 1050px; margin: 40px auto; padding: 0 20px; }

        /* Section Transitions */
        .portal-section { display: none; opacity: 0; transition: opacity 0.4s ease-in-out; }
        .portal-section.active { display: block; opacity: 1; animation: slideUp 0.4s ease forwards; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Branding & Header */
        .school-header { 
            text-align: center; margin-bottom: 30px; padding: 35px 25px; 
            background: #fff; border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-top: 5px solid #3b82f6; 
        }
        .school-header h2 { font-weight: 800; color: #1e293b; margin-top: 15px; letter-spacing: 1px; }
        .school-header p { color: #64748b; font-size: 15px; }

        /* Modern Cards for Hub */
        .hub-card {
            border-radius: 12px; padding: 30px 20px; color: white; cursor: pointer;
            transition: all 0.3s ease; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .hub-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        .hub-card-apply { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .hub-card-track { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .hub-card i { font-size: 48px; margin-bottom: 15px; opacity: 0.9; }
        .hub-card h3 { font-weight: 700; margin: 0 0 10px 0; font-size: 28px; }

        /* Form Styling */
        .box { border-radius: 12px; border-top: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .box-header { border-bottom: 1px solid #f1f5f9; padding: 20px; }
        .box-title { font-weight: 700 !important; color: #334155; }
        .form-section-title { 
            border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 25px; 
            color: #3b82f6; font-weight: 700; text-transform: uppercase; font-size: 14px; margin-top: 30px; letter-spacing: 0.5px;
        }
        .form-section-title:first-child { margin-top: 10px; }
        .required-star { color: #ef4444; }
        .form-control { border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: none; padding: 10px 15px; height: auto; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .input-group-addon { border-radius: 6px 0 0 6px; border-color: #cbd5e1; background: #f8fafc; color: #64748b; }

        /* Result & Letter Styling */
        .status-badge { font-size: 14px; padding: 8px 20px; border-radius: 30px; font-weight: 700; letter-spacing: 1px; }
        #official-letter-content { 
            border: 1px solid #e2e8f0; padding: 60px; background: #fff; 
            margin-top: 30px; border-radius: 8px; font-family: 'Times New Roman', Times, serif; color: #000;
        }

        /* PRINT CONTROL */
        @media print {
            body { background: #fff !important; margin: 0; padding: 0; }
            .portal-wrapper { max-width: 100%; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            #letter-container { display: block !important; }
            #official-letter-content {
                position: absolute; left: 0; top: 0; width: 100%; height: 100%;
                border: none !important; box-shadow: none !important; padding: 20mm !important; margin: 0;
            }
            @page { margin: 0; size: A4 portrait; }
        }
    </style>
</head>
<body>

<div class="portal-wrapper">

    {{-- 1. BRANDING HEADER --}}
    <div class="school-header no-print">
        <img src="{{ asset('images/school_logo.png') }}" style="max-height: 85px;" onerror="this.src='https://via.placeholder.com/90?text={{ env('SCHOOL_ACRONYM', 'LOGO') }}'">
        <h2>{{ env('SCHOOL_NAME', 'MUKAHLERA ACADEMY') }}</h2>
        <p>
            <i class="fa fa-map-marker text-blue"></i> {{ env('SCHOOL_ADDRESS', '123 Education Lane') }} &nbsp;|&nbsp;
            <i class="fa fa-envelope text-blue"></i> {{ env('SCHOOL_EMAIL', 'admissions@school.com') }} &nbsp;|&nbsp;
            <i class="fa fa-phone text-blue"></i> {{ env('SCHOOL_PHONE', '+263 78 724 7792') }}
        </p>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible no-print" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4 style="font-weight: 700;"><i class="icon fa fa-check-circle"></i> Application Submitted!</h4>
            {!! session('success') !!}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible no-print" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(239,68,68,0.2);">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4 style="font-weight: 700;"><i class="icon fa fa-exclamation-triangle"></i> Please correct the following errors:</h4>
            <ul style="margin-bottom: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 2. HUB NAVIGATION --}}
    <div id="hub-section" class="portal-section {{ (!isset($application) && !$errors->any()) ? 'active' : '' }} no-print">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="hub-card hub-card-apply" onclick="showSection('new-app')">
                    <i class="fa fa-graduation-cap"></i>
                    <h3>Apply Now</h3>
                    <p style="font-size: 16px; opacity: 0.9;">Start a new student enrollment application</p>
                    <div style="margin-top: 20px; font-weight: 600;">OPEN FORM <i class="fa fa-arrow-right"></i></div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="hub-card hub-card-track" onclick="showSection('track-app')">
                    <i class="fa fa-search"></i>
                    <h3>Track Status</h3>
                    <p style="font-size: 16px; opacity: 0.9;">Check progress or print admission letters</p>
                    <div style="margin-top: 20px; font-weight: 600;">CHECK PROGRESS <i class="fa fa-arrow-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TRACKING RESULT --}}
    @if(isset($application))
    <div id="section-result" class="portal-section active">
        <a href="{{ route('students.apply') }}" class="btn btn-default no-print" style="margin-bottom: 20px; border-radius: 6px; font-weight: 600;">
            <i class="fa fa-arrow-left"></i> Back to Hub
        </a>

        <div class="box box-solid no-print">
            <div class="box-header with-border" style="background: #f8fafc; border-radius: 12px 12px 0 0;">
                <h3 class="box-title"><i class="fa fa-file-text-o text-blue"></i> Application Reference: <span class="text-blue">{{ $application->tracking_id }}</span></h3>
            </div>
            <div class="box-body" style="padding: 30px;">
                <div class="row">
                    <div class="col-sm-4 text-center">
                        <div style="padding: 30px 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <p class="text-muted text-uppercase" style="font-weight: 700; font-size: 12px; letter-spacing: 1px;">Current Status</p>
                            
                            @if($application->status == 'approved')
                                <i class="fa fa-check-circle" style="font-size: 60px; color: #10b981; margin-bottom: 15px;"></i>
                            @elseif($application->status == 'declined' || $application->status == 'rejected')
                                <i class="fa fa-times-circle" style="font-size: 60px; color: #ef4444; margin-bottom: 15px;"></i>
                            @else
                                <i class="fa fa-hourglass-half" style="font-size: 60px; color: #f59e0b; margin-bottom: 15px;"></i>
                            @endif
                            
                            <br>
                            <span class="label status-badge" style="background-color: {{ $application->status_color ?? ($application->status == 'approved' ? '#10b981' : ($application->status == 'pending' ? '#f59e0b' : '#ef4444')) }};">
                                {{ strtoupper($application->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <table class="table table-striped" style="margin-bottom: 25px;">
                            <tbody>
                                <tr><th width="35%" style="border-top: none;">Student Name:</th><td style="border-top: none; font-weight: 600;">{{ $application->student_name }}</td></tr>
                                <tr><th>Grade Applied:</th><td>{{ $application->applied_grade }}</td></tr>
                                <tr><th>National ID / BC:</th><td><code>{{ $application->identity_number }}</code></td></tr>
                                <tr><th>Submission Date:</th><td>{{ $application->created_at->format('d M Y, h:i A') }}</td></tr>
                                
                                {{-- RENDER UPLOADED FILES AS FULL URLs --}}
                                @if($application->results_file)
                                <tr>
                                    <th>Results / Report Book:</th>
                                    <td>
                                        <a href="{{ asset($application->results_file) }}" target="_blank" class="text-blue" style="word-break: break-all;">
                                            <i class="fa fa-external-link"></i> {{ asset($application->results_file) }}
                                        </a>
                                    </td>
                                </tr>
                                @endif

                                @if($application->recommendation_letter)
                                <tr>
                                    <th>Recommendation Letter:</th>
                                    <td>
                                        <a href="{{ asset($application->recommendation_letter) }}" target="_blank" class="text-blue" style="word-break: break-all;">
                                            <i class="fa fa-external-link"></i> {{ asset($application->recommendation_letter) }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>

                        @if($application->status == 'approved')
                            <button onclick="toggleLetter()" class="btn btn-success btn-lg btn-block" style="border-radius: 8px; font-weight: 700; box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                                <i class="fa fa-envelope-open-o"></i> VIEW & PRINT ADMISSION LETTER
                            </button>
                        @elseif($application->status == 'declined' || $application->status == 'rejected')
                             <div class="callout callout-danger" style="border-radius: 8px;">
                                <h4 style="font-weight: 700;"><i class="fa fa-ban"></i> Application Unsuccessful</h4>
                                <p>{{ $application->admin_remarks ?? 'Unfortunately, we cannot offer a place at this time. Please contact the admissions office for details.' }}</p>
                             </div>
                        @else
                            <div class="callout callout-warning" style="border-radius: 8px; background-color: #fffbeb !important; border-color: #f59e0b !important; color: #b45309 !important;">
                                <h4 style="font-weight: 700;"><i class="fa fa-info-circle"></i> Under Review</h4>
                                <p>Your application is currently being evaluated by our admissions committee. Please check back later.</p>
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
                <div style="border-bottom: 3px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin: 0; font-weight: bold; font-family: 'Times New Roman', serif; font-size: 28px; text-transform: uppercase;">{{ env('SCHOOL_NAME', 'MUKAHLERA ACADEMY') }}</h2>
                        <p style="margin: 5px 0 0 0; font-size: 14px;">
                            {{ env('SCHOOL_ADDRESS') }}<br>
                            Email: {{ env('SCHOOL_EMAIL') }} | Phone: {{ env('SCHOOL_PHONE') }}
                        </p>
                    </div>
                    <div>
                        <img src="{{ asset('images/school_logo.png') }}" style="max-height: 90px;" onerror="this.style.display='none'">
                    </div>
                </div>

                <div style="font-size: 16px; line-height: 1.6;">
                    <p style="float: right;"><strong>Date:</strong> {{ date('d F Y') }}</p>
                    <p><strong>Ref No:</strong> ADM/{{ date('Y') }}/{{ strtoupper(substr($application->tracking_id, -6)) }}</p>
                    <div style="clear: both; margin-bottom: 30px;"></div>
                    
                    <p>To: <strong>{{ $application->guardian_name }}</strong>,<br>
                    Parent/Guardian of <strong>{{ strtoupper($application->student_name) }}</strong></p>

                    <h3 style="text-align: center; font-weight: bold; text-decoration: underline; margin: 40px 0; font-size: 20px;">
                        OFFICIAL OFFER OF PROVISIONAL ADMISSION
                    </h3>

                    <p>Dear Parent/Guardian,</p>
                    <p>Following a comprehensive review of the application submitted, we are pleased to officially inform you that <strong>{{ $application->student_name }}</strong> has been granted provisional admission into <strong>{{ $application->applied_grade }}</strong> for the upcoming academic term.</p>

                    @if($application->admin_remarks)
                        <div style="margin: 25px 0; padding: 20px; border: 1px solid #000; background: #fdfdfd; font-style: italic;">
                            <strong>Admissions Committee Note:</strong><br><br>
                            {{ $application->admin_remarks }}
                        </div>
                    @endif

                    <p>To finalize this enrollment and secure the placement, you are required to present the following documents to the Registrar's office within seven (7) working days from the date of this letter:</p>
                    <ul style="margin-bottom: 20px;">
                        <li>Original National ID or Birth Certificate for verification.</li>
                        <li>Original copies of the most recent academic reports.</li>
                        <li>Proof of payment for the requisite registration fees.</li>
                    </ul>

                    <p>Failure to complete the registration within the stipulated timeframe may result in the forfeiture of this offer.</p>
                    <p>We look forward to welcoming your child to our institution and fostering a successful academic journey.</p>
                    <p>Yours faithfully,</p>

                    <div style="margin-top: 60px; display: flex; justify-content: space-between;">
                        <div style="text-align: left;">
                            <div style="border-bottom: 1px solid #000; width: 250px; margin-bottom: 10px;"></div>
                            <p style="margin: 0;"><strong>Registrar</strong></p>
                            <p style="margin: 0; font-size: 14px;">For: {{ env('SCHOOL_NAME') }}</p>
                        </div>
                        <div style="text-align: center; width: 200px; height: 100px; border: 2px dashed #ccc; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #999;">
                            Official Stamp
                        </div>
                    </div>
                </div>

                <div class="text-center no-print" style="margin-top: 50px;">
                    <button onclick="window.print()" class="btn btn-primary btn-lg" style="border-radius: 30px; padding: 10px 30px; font-weight: bold; box-shadow: 0 4px 15px rgba(59,130,246,0.3);">
                        <i class="fa fa-print"></i> Print Official Letter
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. APPLICATION FORM --}}
    <div id="section-new-app" class="portal-section {{ ($errors->any() && !isset($application)) ? 'active' : '' }} no-print">
        <a href="javascript:void(0)" onclick="showSection('hub-section'); document.getElementById('hub-section').classList.add('active');" class="btn btn-default" style="margin-bottom: 20px; border-radius: 6px; font-weight: 600;"><i class="fa fa-arrow-left"></i> Back to Hub</a>
        
        <div class="box box-solid">
            <div class="box-header with-border" style="background: #f8fafc; border-radius: 12px 12px 0 0; padding: 25px;">
                <h3 class="box-title" style="font-size: 22px;"><i class="fa fa-edit text-blue"></i> New Student Enrollment Form</h3>
            </div>
            
            <form action="{{ route('students.apply.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="box-body" style="padding: 30px;">

                    <div class="form-section-title"><i class="fa fa-user"></i> 1. Student Personal Information</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Full Name (as per Birth Cert) <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-font"></i></span>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>National ID / Birth Cert Number <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                <input type="text" class="form-control" name="identity_number" value="{{ old('identity_number') }}" placeholder="e.g. 12345678D9" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Grade Applied For <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-level-up"></i></span>
                                <select name="grade" class="form-control" required>
                                    <option value="">-- Select Grade --</option>
                                    @foreach(['Form 1', 'Form 2', 'Form 3', 'Form 4', 'Lower 6', 'Upper 6'] as $g)
                                        <option value="{{ $g }}" {{ old('grade') == $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Date of Birth <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="date" class="form-control" name="dob" value="{{ old('dob') }}" max="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Residential Address <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="Physical Home Address" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fa fa-users"></i> 2. Guardian Information</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Guardian Full Name <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
                                <input type="text" class="form-control" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Full Name" required>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Active Phone Number <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="e.g. +263770000000" required>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Email Address <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="For status notifications" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fa fa-book"></i> 3. Academic History & Attachments</div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Previous School Attended <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-institution"></i></span>
                                <input type="text" class="form-control" name="previous_school" value="{{ old('previous_school') }}" placeholder="Name of last school" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Results Summary <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-list-ol"></i></span>
                                <input type="text" class="form-control" name="subjects_passed" placeholder="e.g. 7 Subjects (4As, 3Bs)" value="{{ old('subjects_passed') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Brief Academic/Disciplinary History (Optional)</label>
                        <textarea name="academic_history" class="form-control" rows="3" placeholder="Provide any additional context regarding the student's previous academic or disciplinary record...">{{ old('academic_history') }}</textarea>
                    </div>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6 form-group">
                            <label>Attach Results / Report Book <span class="required-star">*</span></label>
                            <div style="border: 2px dashed #cbd5e1; padding: 15px; border-radius: 6px; background: #f8fafc;">
                                <input type="file" name="results_file" style="width: 100%;" accept=".pdf,.jpg,.jpeg,.png" required>
                                <p class="help-block" style="margin-bottom: 0; margin-top: 10px; font-size: 12px;"><i class="fa fa-info-circle"></i> Max size 4MB. PDF, JPG, or PNG.</p>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Recommendation / Transfer Letter (Optional)</label>
                            <div style="border: 2px dashed #cbd5e1; padding: 15px; border-radius: 6px; background: #f8fafc;">
                                <input type="file" name="recommendation_letter" style="width: 100%;" accept=".pdf,.jpg,.jpeg,.png">
                                <p class="help-block" style="margin-bottom: 0; margin-top: 10px; font-size: 12px;"><i class="fa fa-info-circle"></i> Max size 4MB. PDF, JPG, or PNG.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="box-footer" style="padding: 25px 30px; background: #f8fafc; border-radius: 0 0 12px 12px;">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="border-radius: 8px; font-weight: 700; box-shadow: 0 4px 15px rgba(59,130,246,0.3);" onclick="return confirm('Please confirm all details are correct. Proceed to submit?')">
                        <i class="fa fa-paper-plane"></i> SUBMIT ENROLLMENT APPLICATION
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 5. TRACKING FORM --}}
    <div id="section-track-app" class="portal-section no-print">
        <a href="javascript:void(0)" onclick="showSection('hub-section'); document.getElementById('hub-section').classList.add('active');" class="btn btn-default" style="margin-bottom: 20px; border-radius: 6px; font-weight: 600;"><i class="fa fa-arrow-left"></i> Back to Hub</a>
        
        <div class="box box-solid" style="background: #fff; text-align: center; border-radius: 12px;">
            <div class="box-body" style="padding: 60px 30px;">
                <div style="width: 100px; height: 100px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                    <i class="fa fa-search" style="font-size: 40px; color: #10b981;"></i>
                </div>
                <h2 style="font-weight: 800; color: #1e293b; margin-bottom: 10px;">Track Application</h2>
                <p class="text-muted" style="font-size: 16px; margin-bottom: 35px;">Enter the student's <b>National ID</b> or the <b>Tracking ID</b> received after submission.</p>

                <form action="{{ route('students.apply.track') }}" method="POST">
                    @csrf
                    <div style="max-width: 500px; margin: 0 auto;">
                        <div class="input-group input-group-lg" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 8px;">
                            <span class="input-group-addon" style="background: #fff; border-right: none; border-radius: 8px 0 0 8px;"><i class="fa fa-id-badge text-green"></i></span>
                            <input type="text" name="identity_number" class="form-control" style="border-left: none; text-align: center; font-weight: bold; letter-spacing: 1px; border-radius: 0 8px 8px 0;" placeholder="ID Number or KPC-XXXX-XXXXX" required>
                        </div>
                        <br><br>
                        <button type="submit" class="btn btn-success btn-lg btn-block" style="border-radius: 8px; font-weight: 700; padding: 12px; box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                            <i class="fa fa-chevron-circle-right"></i> CHECK STATUS NOW
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function showSection(id) {
        // Hide all sections instantly
        document.querySelectorAll('.portal-section').forEach(s => {
            s.classList.remove('active');
        });
        
        // Show target section with animation
        const target = document.getElementById('section-' + id);
        if(target) {
            target.classList.add('active');
        }
        
        // Hide hub explicitly
        const hub = document.getElementById('hub-section');
        if(id !== 'hub-section') {
            hub.classList.remove('active');
        }
    }

    function toggleLetter() {
        const letter = document.getElementById('letter-container');
        if (letter.style.display === 'none' || letter.style.display === '') {
            letter.style.display = 'block';
            setTimeout(() => {
                letter.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            letter.style.display = 'none';
        }
    }
</script>
</body>
</html>