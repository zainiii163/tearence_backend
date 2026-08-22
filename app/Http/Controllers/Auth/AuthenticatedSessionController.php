<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $password = trim($credentials['password']);
        $audit = app(LoginAuditService::class);

        Log::info('Login attempt for email: '.$email);

        $adminUser = \App\Models\User::where('email', $email)->first();

        if ($adminUser) {
            $adminPasswordOk = Hash::check($password, $adminUser->password);
            Log::info('Admin user found; password_ok='.($adminPasswordOk ? '1' : '0'));

            if ($adminPasswordOk) {
                Auth::guard('admin-web')->login($adminUser, (bool) $request->boolean('remember'));
                $request->session()->regenerate();
                // Login event listener records success for admin-web
                Log::info('Admin login successful for: '.$adminUser->email);

                return redirect()->intended('/admin');
            }

            $audit->recordAdminFailure($email, 'invalid_credentials', 'admin-web');
        }

        $customer = \App\Models\Customer::where('email', $email)->first();

        if ($customer && Hash::check($password, $customer->password_hash)) {
            Auth::guard('web')->login($customer);
            $request->session()->regenerate();
            $audit->recordCustomerSuccess((string) $customer->email, $customer->customer_id, 'web');
            Log::info('Customer login successful for: '.$customer->email);

            return redirect()->intended('/dashboard');
        }

        if (! $adminUser) {
            $audit->recordCustomerFailure($email, 'invalid_credentials', 'web');
        }

        Log::warning('Authentication failed for email: '.$email.' admin_found='.($adminUser ? '1' : '0').' customer_found='.($customer ? '1' : '0'));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records. Admins: use https://api.worldwideadverts.info/admin and type the password manually (disable browser autofill).',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
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
