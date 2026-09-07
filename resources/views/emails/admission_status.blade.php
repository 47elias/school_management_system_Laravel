<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Inter', 'Source Sans Pro', Helvetica, Arial, sans-serif; 
            line-height: 1.6; 
            color: #334155; 
            background-color: #f0f4f8; 
            margin: 0; 
            padding: 40px 20px; 
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 600px; 
            margin: 0 auto; 
        }
        .container { 
            background: #ffffff;
            padding: 40px; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #f8fafc; 
            padding-bottom: 25px; 
            margin-bottom: 30px;
        }
        .school-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
            display: inline-block;
        }
        .header h2 {
            margin: 0;
            color: #1e293b;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: -0.5px;
        }
        p {
            font-size: 15px;
            margin-bottom: 20px;
            color: #475569;
        }
        .student-name {
            color: #1e293b;
            font-weight: 700;
        }
        .tracking-id {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            font-weight: 600;
        }
        .status-badge { 
            font-weight: 800; 
            text-transform: uppercase; 
            color: #3b82f6; 
            background: #eff6ff;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            letter-spacing: 0.5px;
            display: inline-block;
            border: 1px solid #bfdbfe;
        }
        .remarks-box {
            background: #f8fafc; 
            padding: 20px 25px; 
            border-radius: 0 8px 8px 0; 
            border-left: 4px solid #3b82f6;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .remarks-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .remarks-text {
            color: #475569;
            font-size: 14px;
            margin: 0;
            font-style: italic;
        }
        .footer { 
            font-size: 13px; 
            color: #94a3b8; 
            margin-top: 40px; 
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-weight: 500;
        }
        
        /* Responsive adjustments for mobile email clients */
        @media only screen and (max-width: 600px) {
            body { padding: 20px 10px; }
            .container { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <!-- School Logo -->
                <img src="{{ env('SCHOOL_LOGO_PATH') }}" alt="{{ env('SCHOOL_NAME') }} Logo" class="school-logo" height="100%" width="100%">
                <h2>{{ env('SCHOOL_NAME') }}</h2>
            </div>
            
            <p>Dear Parent/Guardian,</p>
            
            <p>We have reviewed the application for <span class="student-name">{{ strtoupper($admission->student_name) }}</span> (Tracking ID: <span class="tracking-id">#{{ $admission->tracking_id }}</span>).</p>
            
            <p>The status of your application has been updated to:</p>
            <p style="text-align: center; margin: 25px 0;">
                <span class="status-badge">{{ $admission->status }}</span>
            </p>

            @if($admission->admin_remarks)
                <div class="remarks-box">
                    <div class="remarks-title">Remarks from Admissions Office</div>
                    <p class="remarks-text">"{{ $admission->admin_remarks }}"</p>
                </div>
            @endif

            <p style="margin-top: 30px;">If you have any questions or require further assistance regarding this update, please contact the school administration office.</p>
            
            <div class="footer">
                &copy; {{ date('Y') }} {{ env('SCHOOL_NAME') }}. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>