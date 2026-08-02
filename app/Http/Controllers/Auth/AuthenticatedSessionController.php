<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $password = trim($credentials['password']);

        \Log::info('Login attempt for email: ' . $email);

        // First check if it's an admin user
        $adminUser = \App\Models\User::where('email', $email)->first();

        if ($adminUser) {
            $adminPasswordOk = \Hash::check($password, $adminUser->password);
            \Log::info('Admin user found; password_ok=' . ($adminPasswordOk ? '1' : '0'));

            if ($adminPasswordOk) {
                \Illuminate\Support\Facades\Auth::guard('admin-web')->login($adminUser, (bool) $request->boolean('remember'));
                $request->session()->regenerate();

                \Log::info('Admin login successful for: ' . $adminUser->email);

                return redirect()->intended('/admin');
            }
        }

        // Check if it's a customer user
        $customer = \App\Models\Customer::where('email', $email)->first();

        if ($customer && \Hash::check($password, $customer->password_hash)) {
            \Illuminate\Support\Facades\Auth::guard('web')->login($customer);
            $request->session()->regenerate();

            \Log::info('Customer login successful for: ' . $customer->email);

            return redirect()->intended('/dashboard');
        }

        \Log::warning('Authentication failed for email: ' . $email . ' admin_found=' . ($adminUser ? '1' : '0') . ' customer_found=' . ($customer ? '1' : '0'));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records. Admins: use https://api.worldwideadverts.info/admin and type the password manually (disable browser autofill).',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        // Check if user is logged in as admin
        if (\Illuminate\Support\Facades\Auth::guard('admin-web')->check()) {
            \Illuminate\Support\Facades\Auth::guard('admin-web')->logout();
        } else {
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
