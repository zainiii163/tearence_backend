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

    public static function sendForgotPasswordEmail($user, $password) 
    {
        try{
            Mail::to($user->email)->send(new ForgotPasswordMail($user->first_name . ' ' .$user->last_name, $password));
        } catch (\Exception $e) {
            Log::warning("Email reset password not sent. error: " . $e->getMessage());
        }
 
        Log::info("Send forgot password email to : " . $user->email);
    }
}