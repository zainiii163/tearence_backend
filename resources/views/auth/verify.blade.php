<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verification - {{ config('app.name', 'Worldwide Adverts') }}</title>
    <style>
/* ================= ROOT ================= */
:root {
    --primary: #5b5cf6;
    --secondary: #22d3ee;
    --dark: #0f172a;
    --gray: #64748b;
    --light: #f8fafc;
    --white: #ffffff;
}

/* ================= RESET ================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* ================= BACKGROUND ================= */
body {
    min-height: 100vh;
    font-family: 'Segoe UI', system-ui, sans-serif;
    background:
        radial-gradient(circle at top left, #6366f1, transparent 40%),
        radial-gradient(circle at bottom right, #22d3ee, transparent 40%),
        #020617;
}

/* ================= CENTER ================= */
.login-wrapper {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* ================= MAIN BOX ================= */
.login-box {
    width: 100%;
    max-width: 900px;
    background: var(--white);
    border-radius: 18px;
    overflow: hidden;
    box-shadow:
        0 30px 80px rgba(0,0,0,.55),
        0 0 0 1px rgba(255,255,255,.05);
    display: grid;
    grid-template-columns: 1fr 1fr;
}

/* ================= LEFT PANEL ================= */
.login-visual {
    padding: 50px;
    color: white;
    background: linear-gradient(160deg, #6366f1, #22d3ee);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-visual h2 {
    font-size: 32px;
    margin-bottom: 12px;
}

.login-visual p {
    font-size: 15px;
    opacity: .9;
    line-height: 1.6;
}

/* ================= RIGHT PANEL ================= */
.login-form {
    padding: 50px;
}

/* Title */
.login-form h3 {
    font-size: 26px;
    margin-bottom: 6px;
    color: var(--dark);
}

.login-form small {
    color: var(--gray);
}

/* ================= ALERTS ================= */
.alert {
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 14px;
    margin: 18px 0;
}

.alert-danger { background:#fee2e2; color:#991b1b; }
.alert-success { background:#dcfce7; color:#166534; }
.alert-info { background:#e0f2fe; color:#075985; }

/* ================= VERIFICATION ICON ================= */
.verification-icon {
    text-align: center;
    margin: 30px 0;
}

.verification-icon .icon {
    font-size: 64px;
    margin-bottom: 15px;
}

.verification-icon .icon.success {
    color: #10b981;
}

.verification-icon .icon.info {
    color: var(--primary);
}

/* ================= STATUS MESSAGE ================= */
.status-message {
    text-align: center;
    margin: 25px 0;
}

.status-message h3 {
    color: var(--dark);
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 20px;
}

.status-message p {
    color: var(--gray);
    font-size: 14px;
    line-height: 1.5;
}

/* ================= BUTTON ================= */
.btn-login {
    width: 100%;
    padding: 14px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(to right, #6366f1, #22d3ee);
    color: white;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
    margin-top: 18px;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(99,102,241,.45);
}

.btn-secondary {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 2px solid var(--primary);
    background: transparent;
    color: var(--primary);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: .3s;
    margin-top: 12px;
}

.btn-secondary:hover {
    background: var(--primary);
    color: white;
}

/* ================= EXTRA ================= */
.divider {
    margin: 26px 0;
    text-align: center;
    position: relative;
}

.divider::before {
    content: '';
    height: 1px;
    width: 100%;
    background: #e5e7eb;
    position: absolute;
    top: 50%;
    left: 0;
}

.divider span {
    background: white;
    padding: 0 12px;
    font-size: 12px;
    color: var(--gray);
    position: relative;
}

.link {
    color: var(--primary);
    font-weight: 500;
    text-decoration: none;
}

.link:hover { text-decoration: underline; }

/* ================= HELP TEXT ================= */
.help-text {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--gray);
    line-height: 1.4;
}

/* ================= RESPONSIVE ================= */
@media(max-width:900px) {
    .login-box {
        grid-template-columns: 1fr;
    }

    .login-visual {
        display: none;
    }

    .login-form {
        padding: 35px;
    }
}
</style>
</head>
<body>
    <div class="login-wrapper">
    <div class="login-box">

        <!-- LEFT PANEL -->
        <div class="login-visual">
            <h2>Email Verification</h2>
            <p>
                Verify your email address to unlock all features and keep your account secure.
            </p>
        </div>

        <!-- RIGHT PANEL -->
        <div class="login-form">
            <h3>Verify Your Email</h3>
            <small>Complete your registration</small>

            @if(session('verified'))
                <div class="verification-icon">
                    <div class="icon success">✓</div>
                </div>
                
                <div class="status-message">
                    <h3>Email Verified Successfully!</h3>
                    <p>Your email has been verified. You can now use all features of your account.</p>
                </div>
                
                <a href="{{ route('dashboard') }}" class="btn-login">
                    Go to Dashboard
                </a>
            @else
                <div class="verification-icon">
                    <div class="icon info">✉</div>
                </div>

                @if(session('status'))
                    @if(session('status') == 'verification-link-sent')
                        <div class="alert alert-success">
                            Verification link sent! Please check your email.
                        </div>
                    @endif
                @endif

                <div class="status-message">
                    <h3>Check Your Email</h3>
                    <p>We've sent a verification link to your email address. Please click the link to verify your account.</p>
                </div>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        Resend Verification Email
                    </button>
                </form>

                <div class="divider"><span>Need help?</span></div>

                <div class="help-text">
                    • Check your spam folder<br>
                    • Make sure the email address is correct<br>
                    • Wait a few minutes and try again
                </div>
            @endif

            <div class="divider"><span>Back to</span></div>

            <p style="text-align:center;margin-top:18px">
                Return to sign in page
                <a href="{{ route('login') }}" class="link">Sign In</a>
            </p>
        </div>

    </div>
</div>
</body>
</html>