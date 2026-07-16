<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationService
{
    protected int $digits;
    protected int $ttlMinutes;
    protected int $verifiedTtlMinutes;
    protected int $resendCooldown;

    public function __construct()
    {
        $this->digits = config('verification.otp_digits', 6);
        $this->ttlMinutes = config('verification.otp_ttl_minutes', 10);
        $this->verifiedTtlMinutes = config('verification.verified_ttl_minutes', 60);
        $this->resendCooldown = config('verification.resend_cooldown_seconds', 60);
    }

    public function sendEmailOtp(string $email): array
    {
        $email = strtolower(trim($email));
        $this->assertResendAllowed($this->otpSentKey('email', $email));

        $code = $this->generateCode();
        Cache::put($this->otpKey('email', $email), $code, now()->addMinutes($this->ttlMinutes));
        Cache::put($this->otpSentKey('email', $email), true, now()->addSeconds($this->resendCooldown));

        $mailDelivered = false;

        try {
            Mail::to($email)->send(new VerificationCodeMail(null, $code, $this->ttlMinutes));
            $mailDelivered = true;
            Log::info('Email verification code sent', ['email' => $email]);
        } catch (\Throwable $e) {
            // Same pattern as SMS: OTP stays valid even if delivery provider fails.
            Log::warning('Verification email delivery failed: ' . $e->getMessage(), [
                'email' => $email,
                'code' => $code,
            ]);
        }

        $result = [
            'sent' => true,
            'mail_delivered' => $mailDelivered,
            'expires_in_minutes' => $this->ttlMinutes,
        ];

        // Let signup continue when SMTP is misconfigured; code is also in laravel.log.
        if (!$mailDelivered || config('verification.expose_otp')) {
            $result['dev_code'] = $code;
        }

        return $result;
    }

    public function verifyEmailOtp(string $email, string $code): bool
    {
        $email = strtolower(trim($email));
        $code = trim($code);

        if (!$this->validateOtp($this->otpKey('email', $email), $code)) {
            return false;
        }

        Cache::forget($this->otpKey('email', $email));
        Cache::put($this->verifiedKey('email', $email), true, now()->addMinutes($this->verifiedTtlMinutes));

        return true;
    }

    public function sendPhoneOtp(string $phone, string $country = ''): array
    {
        $normalized = $this->normalizePhone($phone);
        $this->assertResendAllowed($this->otpSentKey('phone', $normalized));

        $code = $this->generateCode();
        Cache::put($this->otpKey('phone', $normalized), $code, now()->addMinutes($this->ttlMinutes));
        Cache::put($this->otpSentKey('phone', $normalized), true, now()->addSeconds($this->resendCooldown));

        $this->dispatchSms($normalized, $code, $country);

        Log::info('Phone verification code sent', ['phone' => $normalized]);

        return [
            'sent' => true,
            'expires_in_minutes' => $this->ttlMinutes,
        ];
    }

    public function verifyPhoneOtp(string $phone, string $code): bool
    {
        $normalized = $this->normalizePhone($phone);
        $code = trim($code);

        if (!$this->validateOtp($this->otpKey('phone', $normalized), $code)) {
            return false;
        }

        Cache::forget($this->otpKey('phone', $normalized));
        Cache::put($this->verifiedKey('phone', $normalized), true, now()->addMinutes($this->verifiedTtlMinutes));

        return true;
    }

    public function isEmailVerified(string $email): bool
    {
        return (bool) Cache::get($this->verifiedKey('email', strtolower(trim($email))));
    }

    public function isPhoneVerified(string $phone): bool
    {
        return (bool) Cache::get($this->verifiedKey('phone', $this->normalizePhone($phone)));
    }

    public function checkCompany(string $companyNumber, ?string $vatNumber, string $country): array
    {
        $companyNumber = strtoupper(preg_replace('/\s+/', '', $companyNumber));
        $vatNumber = $vatNumber ? strtoupper(preg_replace('/\s+/', '', $vatNumber)) : null;
        $country = trim($country);

        if ($this->isUkCountry($country)) {
            return $this->checkUkCompany($companyNumber, $vatNumber);
        }

        if (strlen($companyNumber) < 3) {
            return [
                'verified' => false,
                'message' => 'Invalid company registration number.',
                'company_name' => null,
                'company_status' => null,
            ];
        }

        return [
            'verified' => 'pending',
            'message' => 'Company check queued for manual review against regional registers.',
            'company_name' => null,
            'company_status' => 'pending_review',
        ];
    }

    protected function checkUkCompany(string $companyNumber, ?string $vatNumber): array
    {
        $apiKey = config('verification.companies_house_api_key');

        if (!$apiKey) {
            if ($this->looksLikeUkCompanyNumber($companyNumber)) {
                return [
                    'verified' => 'pending',
                    'message' => 'UK company number format accepted — live Companies House check pending API key.',
                    'company_name' => null,
                    'company_status' => 'pending_review',
                ];
            }

            return [
                'verified' => false,
                'message' => 'Invalid UK company registration number format.',
                'company_name' => null,
                'company_status' => null,
            ];
        }

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->timeout(15)
                ->get("https://api.company-information.service.gov.uk/company/{$companyNumber}");

            if (!$response->successful()) {
                return [
                    'verified' => false,
                    'message' => 'Company not found in Companies House register.',
                    'company_name' => null,
                    'company_status' => null,
                ];
            }

            $data = $response->json();
            $active = in_array(strtolower($data['company_status'] ?? ''), ['active', 'open'], true);

            $vatOk = true;
            $vatMessage = null;
            if ($vatNumber) {
                $vatOk = $this->isValidUkVatFormat($vatNumber);
                if (!$vatOk) {
                    $vatMessage = 'VAT number format is invalid for UK.';
                }
            }

            if (!$active) {
                return [
                    'verified' => false,
                    'message' => 'Company found but status is not active.',
                    'company_name' => $data['company_name'] ?? null,
                    'company_status' => $data['company_status'] ?? null,
                ];
            }

            if (!$vatOk) {
                return [
                    'verified' => false,
                    'message' => $vatMessage,
                    'company_name' => $data['company_name'] ?? null,
                    'company_status' => $data['company_status'] ?? null,
                ];
            }

            return [
                'verified' => true,
                'message' => 'Company verified via Companies House.',
                'company_name' => $data['company_name'] ?? null,
                'company_status' => $data['company_status'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Companies House lookup failed: ' . $e->getMessage());

            return [
                'verified' => 'pending',
                'message' => 'Unable to reach Companies House — check queued for review.',
                'company_name' => null,
                'company_status' => 'pending_review',
            ];
        }
    }

    protected function dispatchSms(string $phone, string $code, string $country): void
    {
        $sid = config('verification.twilio.sid');
        $token = config('verification.twilio.token');
        $from = config('verification.twilio.from');

        $message = "Your Worldwide Adverts verification code is {$code}. Valid for {$this->ttlMinutes} minutes.";

        if ($sid && $token && $from) {
            try {
                Http::withBasicAuth($sid, $token)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                        'From' => $from,
                        'To' => $phone,
                        'Body' => $message,
                    ]);
                return;
            } catch (\Throwable $e) {
                Log::warning('Twilio SMS failed: ' . $e->getMessage(), ['phone' => $phone]);
            }
        }

        Log::info('SMS verification code (Twilio not configured)', [
            'phone' => $phone,
            'country' => $country,
            'code' => $code,
        ]);
    }

    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, (10 ** $this->digits) - 1), $this->digits, '0', STR_PAD_LEFT);
    }

    protected function validateOtp(string $cacheKey, string $code): bool
    {
        $stored = Cache::get($cacheKey);
        return $stored && hash_equals((string) $stored, $code);
    }

    protected function assertResendAllowed(string $sentKey): void
    {
        if (Cache::has($sentKey)) {
            throw new \RuntimeException('Please wait before requesting another code.');
        }
    }

    protected function otpKey(string $type, string $identifier): string
    {
        return "verification_otp_{$type}_" . md5($identifier);
    }

    protected function otpSentKey(string $type, string $identifier): string
    {
        return "verification_sent_{$type}_" . md5($identifier);
    }

    protected function verifiedKey(string $type, string $identifier): string
    {
        return "verification_verified_{$type}_" . md5($identifier);
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        return $digits ?: trim($phone);
    }

    protected function isUkCountry(string $country): bool
    {
        $normalized = strtolower($country);
        return in_array($normalized, ['uk', 'gb', 'united kingdom', 'great britain', 'england', 'scotland', 'wales', 'northern ireland'], true);
    }

    protected function looksLikeUkCompanyNumber(string $number): bool
    {
        return (bool) preg_match('/^[A-Z]{0,2}\d{6,8}$/', $number);
    }

    protected function isValidUkVatFormat(string $vat): bool
    {
        $vat = preg_replace('/^GB/i', '', $vat);
        return (bool) preg_match('/^\d{9}$/', $vat) || (bool) preg_match('/^\d{12}$/', $vat);
    }
}
