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

        // Debug: Log the login attempt
        \Log::info('Login attempt for email: ' . $credentials['email']);

        // First check if it's an admin user
        $adminUser = \App\Models\User::where('email', $credentials['email'])->first();
        
        if ($adminUser && \Hash::check($credentials['password'], $adminUser->password)) {
            // Authenticate admin user with session using attempt method
            if (Auth::guard('admin-web')->attempt($credentials)) {
                $request->session()->regenerate();
                
                \Log::info('Admin login successful for: ' . $adminUser->email);
                
                // Redirect to admin dashboard
                return redirect()->intended('/admin');
            }
        }

        // Check if it's a customer user
        $customer = \App\Models\Customer::where('email', $credentials['email'])->first();
        
        if ($customer && \Hash::check($credentials['password'], $customer->password)) {
            // Authenticate customer user
            Auth::guard('web')->attempt($credentials);
            $request->session()->regenerate();
            
            \Log::info('Customer login successful for: ' . $customer->email);
            
            // Redirect to customer dashboard
            return redirect()->intended('/dashboard');
        }
        
        // If we reach here, authentication failed
        \Log::warning('Authentication failed for email: ' . $credentials['email']);
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
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
        if (Auth::guard('admin-web')->check()) {
            Auth::guard('admin-web')->logout();
        } else {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
