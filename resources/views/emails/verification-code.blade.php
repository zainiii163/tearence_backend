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
    <p>This code expires in {{ $expiresIn }} minutes. If you did not request this, you can ignore this email.</p>
    <p>— Worldwide Adverts</p>
</body>
</html>
