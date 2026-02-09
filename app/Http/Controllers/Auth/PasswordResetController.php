<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset link request form.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
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

        // Generate a new password
        $newPassword = strtoupper(substr(md5(time()), 0, 8));
        
        // Update user password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Send email with new password
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user->first_name, $newPassword));
            
            return back()->with('status', 'We have emailed your new password!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Failed to send password reset email. Please try again.',
            ]);
        }
    }
}
