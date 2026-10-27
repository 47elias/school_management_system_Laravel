<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Password reset code</title></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;padding:32px 16px">
    <div style="max-width:480px;margin:auto;background:#fff;border-radius:16px;padding:32px;border-top:4px solid #2563eb">
        <h1 style="font-size:22px;margin:0 0 16px">Reset your staff password</h1>
        <p style="line-height:1.6;color:#475569">Enter this code on the password reset page to choose a new password.</p>
        <p style="padding:20px;background:#eff6ff;text-align:center;font-size:32px;letter-spacing:8px;font-weight:bold;color:#1d4ed8">{{ $code }}</p>
        <p style="line-height:1.6;color:#475569">This code expires in <strong>10 minutes</strong> and can only be used once. Do not share it with anyone.</p>
        <p style="font-size:13px;line-height:1.6;color:#64748b">If you did not request a password reset, you can ignore this email. Your password has not changed.</p>
        <p style="font-size:13px;color:#64748b">{{ config('app.name', 'School Portal') }}</p>
    </div>
</body>
</html>
