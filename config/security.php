<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security login alerts (Clive: know every login / attempt + where from)
    |--------------------------------------------------------------------------
    */
    'login_alerts_enabled' => env('SECURITY_LOGIN_ALERTS_ENABLED', true),

    // Always email these addresses (comma-separated in .env)
    'alert_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SECURITY_ALERT_EMAILS', ''))
    ))),

    // Also email every super admin + users with can_view_security_logs
    'email_security_staff' => env('SECURITY_EMAIL_STAFF', true),

    // Alert on every admin/backend login (success or fail)
    'alert_all_admin_logins' => env('SECURITY_ALERT_ALL_ADMIN_LOGINS', true),

    // Failed customer attempts in window before alert
    'failed_attempt_threshold' => (int) env('SECURITY_FAILED_LOGIN_THRESHOLD', 3),
    'failed_attempt_window_minutes' => (int) env('SECURITY_FAILED_LOGIN_WINDOW', 15),
];
