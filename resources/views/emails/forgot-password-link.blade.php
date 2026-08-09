<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;background:#f8fafc;padding:24px;color:#0f172a;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:28px;border:1px solid #e2e8f0;">
        <h2 style="margin:0 0 12px;">Reset your password</h2>
        <p style="margin:0 0 16px;">Hi {{ $name }},</p>
        <p style="margin:0 0 16px;">We received a request to reset your Worldwide Adverts password. Click the button below — this link expires in 60 minutes.</p>
        <p style="margin:24px 0;">
            <a href="{{ $resetUrl }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:600;">
                Reset password
            </a>
        </p>
        <p style="margin:0 0 8px;font-size:13px;color:#64748b;">If the button doesn’t work, copy this link:</p>
        <p style="margin:0 0 16px;font-size:12px;word-break:break-all;color:#475569;">{{ $resetUrl }}</p>
        <p style="margin:0;font-size:13px;color:#64748b;">If you didn’t request this, you can ignore this email. Your password will stay the same.</p>
    </div>
</body>
</html>
