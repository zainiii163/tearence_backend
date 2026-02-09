<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Worldwide Adverts') }}</title>
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

/* ================= FORM ================= */
.form-group {
    margin-top: 18px;
}

.form-label {
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
    color: var(--dark);
    font-weight: 500;
}

.form-input {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    transition: .25s;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.25);
}

.is-invalid { border-color:#ef4444; }

.invalid-feedback {
    font-size: 12px;
    color: #ef4444;
    margin-top: 4px;
}

/* ================= PASSWORD ================= */
.form-group-icon {
    position: relative;
}

.toggle-pass {
    position: absolute;
    right: 14px;
    top: 52%;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 16px;
}

/* ================= OPTIONS ================= */
.options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 18px 0;
    font-size: 14px;
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
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(99,102,241,.45);
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
                <h2>Worldwide Adverts</h2>
                <p>
                    Manage campaigns, monitor analytics and grow your brand
                    using our powerful advertising platform.
                </p>
            </div>

            <!-- RIGHT PANEL -->
            <div class="login-form">
                <h3>Welcome Back</h3>
                <small>Login to continue</small>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="alert alert-info">
                    <strong>Admin Access</strong><br>
                    rizky@worldwideadverts.info | admin123
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group form-group-icon">
                        <label class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" required>
                        <button type="button" class="toggle-pass" onclick="togglePassword()">👁</button>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="options">
                        <label><input type="checkbox" name="remember"> Remember me</label>
                        <a href="{{ route('password.request') }}" class="link">Forgot?</a>
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>

                    <div class="divider"><span>OR</span></div>

                    <a href="{{ route('otp.send') }}" class="btn-login" style="background:#0f172a">
                        Login with OTP
                    </a>

                    <p style="text-align:center;margin-top:18px">
                        New here?
                        <a href="{{ route('register') }}" class="link">Create account</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(){
        const p = document.getElementById('password');
        p.type = p.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>
