<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verification code</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello{{ $name ? ' ' . $name : '' }},</p>
    <p>Your Worldwide Adverts verification code is:</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px;">{{ $code }}</p>
    <p style="margin-top: 16px;">
        <a href="{{ $verifyUrl }}"
           style="display:inline-block;padding:12px 24px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">
            Verify my email
        </a>
    </p>
    <p style="margin-top: 12px; font-size: 13px; color: #64748b;">
        Or copy this link: <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
    </p>
    <p>This code expires in {{ $expiresIn }} minutes. If you did not request this, you can ignore this email.</p>
    <p>— Worldwide Adverts</p>
</body>
</html>
