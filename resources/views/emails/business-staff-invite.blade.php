<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Team invite</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
  <div style="max-width: 560px; margin: 0 auto; padding: 24px;">
    <h2 style="margin: 0 0 12px;">You're invited to manage a business</h2>
    <p>
      <strong>{{ $inviterName }}</strong> invited <strong>{{ $inviteeEmail }}</strong>
      to join <strong>{{ $businessName }}</strong> on Worldwide Adverts
      as <strong>{{ $role }}</strong>.
    </p>
    <p style="margin: 20px 0;">
      <a href="{{ $signupUrl }}"
         style="display:inline-block;background:#0f172a;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
        Create account / Sign in
      </a>
    </p>
    <p style="font-size: 14px; color: #475569;">
      After you register with this email, open this link to join the team:<br>
      <a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a>
    </p>
    <p style="font-size: 12px; color: #94a3b8; margin-top: 28px;">
      Worldwide Adverts — business team invite
    </p>
  </div>
</body>
</html>
