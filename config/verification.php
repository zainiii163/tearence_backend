<?php

return [
    'otp_digits' => (int) env('VERIFICATION_OTP_DIGITS', 6),
    'otp_ttl_minutes' => (int) env('VERIFICATION_OTP_TTL', 10),
    'verified_ttl_minutes' => (int) env('VERIFICATION_VERIFIED_TTL', 60),
    'resend_cooldown_seconds' => (int) env('VERIFICATION_RESEND_COOLDOWN', 60),

    /** When true, OTP is included in API response (for testing). Auto-on when mail fails. */
    'expose_otp' => (bool) env('VERIFICATION_EXPOSE_OTP', false),

    /** Frontend URL for password-reset links and verification pages */
    'frontend_url' => env('FRONTEND_URL', 'https://worldwideadverts.info'),

    /** Require email verification before allowing posts (default true) */
    'email_required' => (bool) env('EMAIL_VERIFICATION_REQUIRED', true),

    'companies_house_api_key' => env('COMPANIES_HOUSE_API_KEY'),

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],
];
