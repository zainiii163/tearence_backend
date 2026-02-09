<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome - {{ config('app.name', 'Worldwide Adverts') }}</title>
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

/* ================= WELCOME ICON ================= */
.welcome-icon {
    text-align: center;
    margin: 30px 0;
}

.welcome-icon .icon {
    font-size: 64px;
    margin-bottom: 15px;
    color: var(--primary);
}

/* ================= USER INFO ================= */
.user-info {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    text-align: center;
}

.user-info h3 {
    color: var(--dark);
    margin: 0 0 8px 0;
    font-size: 18px;
}

.user-info p {
    color: var(--gray);
    margin: 4px 0;
    font-size: 13px;
}

.user-info .user-email {
    color: var(--primary);
    font-weight: 600;
}

/* ================= WELCOME MESSAGE ================= */
.welcome-message {
    text-align: center;
    margin: 25px 0;
}

.welcome-message h2 {
    color: var(--dark);
    font-weight: 600;
    margin-bottom: 12px;
    font-size: 20px;
}

.welcome-message p {
    color: var(--gray);
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 8px;
}

/* ================= FEATURES ================= */
.features-list {
    margin: 30px 0;
}

.features-list h3 {
    color: var(--dark);
    font-weight: 600;
    margin-bottom: 20px;
    text-align: center;
    font-size: 16px;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: #e2e8f0;
    transform: translateX(3px);
}

.feature-item .icon {
    font-size: 20px;
    color: var(--primary);
    margin-right: 12px;
    min-width: 24px;
}

.feature-item-content h4 {
    margin: 0 0 4px 0;
    color: var(--dark);
    font-weight: 600;
    font-size: 14px;
}

.feature-item-content p {
    margin: 0;
    color: var(--gray);
    font-size: 12px;
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
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(99,102,241,.45);
    text-decoration: none;
    color: white;
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
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-secondary:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
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
            <h2>Welcome to Worldwide Adverts!</h2>
            <p>
                We're excited to have you join our community of buyers, sellers, 
                and service providers from around the world.
            </p>
        </div>

        <!-- RIGHT PANEL -->
        <div class="login-form">
            <h3>Your Journey Starts Here</h3>
            <small>Let's get you started</small>

            <div class="welcome-icon">
                <div class="icon">🚀</div>
            </div>

            @if(auth()->check())
                <div class="user-info">
                    <h3>Welcome, {{ auth()->user()->first_name }}!</h3>
                    <p class="user-email">{{ auth()->user()->email }}</p>
                    <p>Member since: {{ auth()->user()->created_at->format('M d, Y') }}</p>
                </div>
            @endif

            <div class="welcome-message">
                <h2>What You Can Do Next</h2>
                <p>Thank you for joining Worldwide Adverts! Get ready to explore amazing opportunities.</p>
            </div>

            <div class="features-list">
                <h3>Quick Actions</h3>
                
                <div class="feature-item">
                    <div class="icon">+</div>
                    <div class="feature-item-content">
                        <h4>Create Your First Listing</h4>
                        <p>Post items, services, or opportunities</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon">🔍</div>
                    <div class="feature-item-content">
                        <h4>Browse Categories</h4>
                        <p>Discover deals and services</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon">🛡️</div>
                    <div class="feature-item-content">
                        <h4>Complete Your Profile</h4>
                        <p>Add details for better visibility</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="icon">👥</div>
                    <div class="feature-item-content">
                        <h4>Connect with Others</h4>
                        <p>Join our community today</p>
                    </div>
                </div>
            </div>

            @if(auth()->check())
                <a href="{{ route('dashboard') }}" class="btn-login">
                    Go to Dashboard
                </a>
                
                <a href="/create-listing" class="btn-secondary">
                    Create Your First Listing
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-login">
                    Sign In to Get Started
                </a>
                
                <a href="{{ route('register') }}" class="btn-secondary">
                    Create Account
                </a>
            @endif

            <div class="divider"><span>Need help?</span></div>

            <p style="text-align:center;margin-top:18px">
                Contact our support team
                <a href="#" class="link">Get Support</a>
            </p>
        </div>

    </div>
</div>
</body>
</html>