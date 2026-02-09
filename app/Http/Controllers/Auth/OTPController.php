<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class OTPController extends Controller
{
    /**
     * Display the OTP verification form.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('auth.otp');
    }

    /**
     * Send OTP to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We can\'t find a user with that email address.',
            ]);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in cache for 10 minutes
        Cache::put('otp_' . $user->email, $otp, 600); // 10 minutes

        try {
            Mail::to($user->email)->send(new OTPMail($user->first_name, $otp));
            
            return redirect()->route('otp.verify')->with('email', $user->email);
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Failed to send OTP. Please try again.',
            ]);
        }
    }

    /**
     * Verify the OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $cachedOtp = Cache::get('otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP.',
            ]);
        }

        // OTP is valid, remove it from cache
        Cache::forget('otp_' . $request->email);

        // Log the user in
        $user = User::where('email', $request->email)->first();
        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Successfully logged in with OTP!');
    }
}
