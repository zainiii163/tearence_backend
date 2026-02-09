<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'Worldwide Adverts') }}</title>
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 18px;
}

.form-label {
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
    color: var(--dark);
    font-weight: 500;
}

.form-input, .form-select {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    transition: .25s;
    background: var(--white);
}

.form-input:focus, .form-select:focus {
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
    margin-top: 24px;
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
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}
</style>
</head>
<body>
    <div class="login-wrapper">
    <div class="login-box">

        <!-- LEFT PANEL -->
        <div class="login-visual">
            <h2>Join Worldwide Adverts</h2>
            <p>
                Create your account and start connecting with buyers, sellers, 
                and service providers from around the world.
            </p>
        </div>

        <!-- RIGHT PANEL -->
        <div class="login-form">
            <h3>Create Account</h3>
            <small>Join our community today</small>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-input @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-input @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="">Select your timezone</option>
                        <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                        <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                        <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                        <option value="Asia/Kolkata" {{ old('timezone') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata</option>
                        <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                    </select>
                </div>

                <button class="btn-login">Create Account</button>

                <div class="divider"><span>Already have an account?</span></div>

                <p style="text-align:center;margin-top:18px">
                    Sign in to your existing account
                    <a href="{{ route('login') }}" class="link">Sign in</a>
                </p>
            </form>
        </div>

    </div>
</div>
</body>
</html>
