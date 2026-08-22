<?php

namespace App\Helpers;

use App\Mail\OtpMail;
use App\Mail\RegisterMail;
use App\Mail\WelcomeMail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    public static function sendOtpEmail($user, $otp) 
    {
        try{
            Mail::to($user->email)->send(new OtpMail($user->first_name . ' ' .$user->last_name, $otp));
        } catch (\Exception $e) {
            Log::warning("Email password not sent. error: " . $e->getMessage());
        }
 
        Log::info("Send otp email to : " . $user->email . " otp: " . $otp);
    }

    public static function sendRegisterEmail($user) 
    {
        try{
            Mail::to($user->email)->send(new RegisterMail($user->first_name . ' ' .$user->last_name, $user->verification_token));
        } catch (\Exception $e) {
            Log::warning("Email register not sent. error: " . $e->getMessage());
        }
 
        Log::info("Send register email to : " . $user->email);
    }

    public static function sendWelcomeEmail($user) 
    {
        try{
            Mail::to($user->email)->send(new WelcomeMail($user->first_name . ' ' .$user->last_name));
        } catch (\Exception $e) {
            Log::warning("Email welcome not sent. error: " . $e->getMessage());
        }
 
        Log::info("Send welcome email to : " . $user->email);
    }

    public static function sendForgotPasswordEmail($user, $resetUrl)
    {
        try {
            \App\Jobs\SendQueuedMailable::dispatch(
                $user->email,
                new ForgotPasswordMail($user->first_name.' '.$user->last_name, $resetUrl)
            );
        } catch (\Exception $e) {
            Log::warning('Email reset password not sent. error: '.$e->getMessage());
            try {
                Mail::to($user->email)->send(
                    new ForgotPasswordMail($user->first_name.' '.$user->last_name, $resetUrl)
                );
            } catch (\Exception $inner) {
                Log::warning('Email reset password sync fallback failed: '.$inner->getMessage());
            }
        }

        Log::info('Send forgot password reset link to : '.$user->email);
    }

    /**
     * Clive: email Super Admin / IT when logins or attempts need attention.
     */
    public static function sendSecurityLoginAlert(string $message, array $details = []): void
    {
        if (! config('security.login_alerts_enabled', true)) {
            return;
        }

        $recipients = config('security.alert_emails', []);

        if (config('security.email_security_staff', true)) {
            try {
                $staff = \App\Models\User::query()
                    ->where(function ($q) {
                        $q->where('is_super_admin', true);
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'can_view_security_logs')) {
                            $q->orWhere('can_view_security_logs', true);
                        }
                    })
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->all();
                $recipients = array_merge($recipients, $staff);
            } catch (\Throwable $e) {
                Log::debug('Security staff email lookup skipped: '.$e->getMessage());
            }
        }

        $recipients = array_values(array_unique(array_filter(array_map('strtolower', $recipients))));
        if ($recipients === []) {
            Log::info('Security login alert (no recipients configured): '.$message);

            return;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new \App\Mail\SecurityLoginAlertMail($message, $details));
            } catch (\Exception $e) {
                Log::warning('Security login alert email failed for '.$email.': '.$e->getMessage());
            }
        }

        Log::info('Security login alert emailed to: '.implode(', ', $recipients));
    }
}