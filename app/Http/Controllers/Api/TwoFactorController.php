<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TotpHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class TwoFactorController extends Controller
{
    public function status(Request $request)
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => !empty($user->two_factor_confirmed_at),
                'confirmed_at' => $user->two_factor_confirmed_at,
            ],
        ]);
    }

    public function setup(Request $request)
    {
        $user = auth('api')->user();

        if ($user->two_factor_confirmed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is already enabled.',
            ], 422);
        }

        $secret = TotpHelper::generateSecret();
        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_confirmed_at = null;
        $user->save();

        $otpAuthUrl = TotpHelper::getOtpAuthUrl($secret, $user->email);

        return response()->json([
            'success' => true,
            'message' => 'Scan the QR code with your authenticator app, then confirm with a code.',
            'data' => [
                'secret' => $secret,
                'otpauth_url' => $otpAuthUrl,
                'qr_code_url' => TotpHelper::getQrCodeUrl($otpAuthUrl),
            ],
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = auth('api')->user();
        if (!$user->two_factor_secret) {
            return response()->json([
                'success' => false,
                'message' => 'Run setup first.',
            ], 422);
        }

        $secret = decrypt($user->two_factor_secret);
        if (!TotpHelper::verify($secret, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication code.',
            ], 422);
        }

        $recovery = TotpHelper::generateRecoveryCodes();
        $user->two_factor_recovery_codes = array_map(
            fn ($c) => Hash::make($c),
            $recovery
        );
        $user->two_factor_confirmed_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication enabled. Store recovery codes safely.',
            'data' => [
                'recovery_codes' => $recovery,
                'enabled' => true,
            ],
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string',
        ]);

        $user = auth('api')->user();
        if (!$user->two_factor_confirmed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.',
            ], 422);
        }

        if (!$this->verifyUserCode($user, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication or recovery code.',
            ], 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication disabled.',
            'data' => ['enabled' => false],
        ]);
    }

    /**
     * Complete login after password step when 2FA is required.
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'pending_token' => 'required|string',
            'code' => 'required|string',
        ]);

        $cacheKey = '2fa_pending_' . $request->pending_token;
        $payload = Cache::get($cacheKey);

        if (!$payload || empty($payload['customer_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Login session expired. Please sign in again.',
            ], 401);
        }

        $user = Customer::find($payload['customer_id']);
        if (!$user || !$user->two_factor_confirmed_at) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify two-factor login.',
            ], 401);
        }

        if (!$this->verifyUserCode($user, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication or recovery code.',
            ], 422);
        }

        Cache::forget($cacheKey);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => [
                    'id' => $user->customer_id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'user_type' => $user->user_type ?? 'basic',
                    'two_factor_enabled' => true,
                ],
            ],
        ]);
    }

    public static function createPendingLogin(Customer $user): string
    {
        $token = Str::random(64);
        Cache::put('2fa_pending_' . $token, [
            'customer_id' => $user->customer_id,
        ], now()->addMinutes(10));

        return $token;
    }

    private function verifyUserCode(Customer $user, string $code): bool
    {
        $code = trim($code);
        if ($user->two_factor_secret) {
            try {
                $secret = decrypt($user->two_factor_secret);
                if (TotpHelper::verify($secret, $code)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // fall through to recovery
            }
        }

        $hashed = $user->two_factor_recovery_codes ?? [];
        if (!is_array($hashed)) {
            return false;
        }

        foreach ($hashed as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashed[$index]);
                $user->two_factor_recovery_codes = array_values($hashed);
                $user->save();
                return true;
            }
        }

        return false;
    }
}
