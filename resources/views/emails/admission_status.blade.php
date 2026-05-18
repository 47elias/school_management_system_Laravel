<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; }
        .container { padding: 20px; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; }
        .status { font-weight: bold; text-transform: uppercase; color: #4f46e5; }
        .footer { font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ env('SCHOOL_NAME') }}</h2>
        </div>
        <p>Dear Parent/Guardian,</p>
        <p>We have reviewed the application for <strong>{{ $admission->student_name }}</strong> (Tracking ID: #{{ $admission->tracking_id }}).</p>
        
        <p>The status of your application has been updated to: <span class="status">{{ $admission->status }}</span></p>

        @if($admission->admin_remarks)
            <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #e2e8f0;">
                <strong>Remarks from Admissions Office:</strong><br>
                {{ $admission->admin_remarks }}
            </div>
        @endif

        <p>If you have any questions, please contact the school administration office.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ env('SCHOOL_NAME') }}. All rights reserved.
        </div>
    </div>
</body>
</html>