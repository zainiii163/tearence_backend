@extends('layouts.app')

@section('styles')
<style>
.otp-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.otp-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    max-width: 450px;
    width: 100%;
}

.otp-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.otp-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 28px;
}

.otp-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.otp-body {
    padding: 40px 30px;
}

.form-floating {
    position: relative;
    margin-bottom: 25px;
}

.form-floating label {
    position: absolute;
    top: 50%;
    left: 15px;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 14px;
    transition: all 0.3s ease;
    pointer-events: none;
    background: white;
    padding: 0 5px;
}

.form-floating input:focus + label,
.form-floating input:not(:placeholder-shown) + label {
    top: -10px;
    left: 10px;
    font-size: 12px;
    color: #667eea;
}

.form-floating input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    text-align: center;
    letter-spacing: 2px;
    font-weight: 600;
}

.form-floating input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.form-floating input.is-invalid {
    border-color: #dc3545;
    background: white;
}

.otp-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 25px;
}

.otp-input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 24px;
    font-weight: 600;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.otp-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.btn-otp {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}

.btn-otp:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.btn-resend {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-resend:hover {
    background: #667eea;
    color: white;
}

.btn-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.btn-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
    display: block;
    text-align: center;
}

.alert {
    border-radius: 10px;
    border: none;
    padding: 15px;
    margin-bottom: 20px;
}

.countdown {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 15px;
}

.countdown-timer {
    color: #667eea;
    font-weight: 600;
}
</style>
@endsection

@section('content')
<div class="otp-container">
    <div class="otp-card">
        <div class="otp-header">
            <h2>OTP Verification</h2>
            <p>Enter the 6-digit code sent to your email</p>
        </div>

        <div class="otp-body">
            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('email'))
                <div class="alert alert-info" role="alert">
                    Verification code sent to: {{ session('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify.post') }}" id="otpForm">
                @csrf
                
                <input type="hidden" name="email" value="{{ session('email') ?? old('email') }}">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" name="otp1" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp2" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp3" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp4" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp5" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp6" required>
                    <input type="hidden" name="otp" id="otp-hidden">
                </div>

                @error('otp')
                    <div class="invalid-feedback">
                        <strong>{{ $message }}</strong>
                    </div>
                @enderror

                <button type="submit" class="btn btn-otp">
                    <i class="fas fa-shield-alt me-2"></i> Verify Code
                </button>
            </form>

            <div class="countdown">
                Resend code in <span class="countdown-timer" id="countdown">2:00</span>
            </div>

            <button type="button" class="btn btn-resend" id="resendBtn" disabled>
                <i class="fas fa-redo me-2"></i> Resend Code
            </button>

            <div class="text-center mt-4">
                <p class="mb-0">Back to 
                    <a href="{{ route('login') }}" class="btn-link">
                        Sign In
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // OTP Input Handling
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otp-hidden');
    const form = document.getElementById('otpForm');
    
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            if (e.target.value.length === 1) {
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            }
            updateOtpHidden();
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && e.target.value === '') {
                if (index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });
        
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').slice(0, 6);
            const digits = pastedData.split('');
            
            digits.forEach((digit, i) => {
                if (i < otpInputs.length && /\d/.test(digit)) {
                    otpInputs[i].value = digit;
                }
            });
            
            const lastFilledIndex = Math.min(digits.length - 1, otpInputs.length - 1);
            otpInputs[lastFilledIndex].focus();
            updateOtpHidden();
        });
    });
    
    function updateOtpHidden() {
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        otpHidden.value = otp;
    }
    
    // Countdown Timer
    let timeLeft = 120; // 2 minutes in seconds
    const countdownElement = document.getElementById('countdown');
    const resendBtn = document.getElementById('resendBtn');
    
    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            countdownElement.textContent = '0:00';
            resendBtn.disabled = false;
        }
        
        timeLeft--;
    }
    
    const countdownInterval = setInterval(updateCountdown, 1000);
    
    // Resend OTP
    resendBtn.addEventListener('click', function() {
        const email = document.querySelector('input[name="email"]').value;
        if (email) {
            // Submit to send OTP
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("otp.send.post") }}';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'email';
            emailInput.value = email;
            form.appendChild(emailInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    // Form submission animation
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('.btn-otp');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';
        submitBtn.disabled = true;
    });
});
</script>
@endsection