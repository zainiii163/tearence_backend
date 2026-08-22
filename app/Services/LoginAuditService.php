<?php

namespace App\Services;

use App\Helpers\MailHelper;
use App\Models\AdminNotification;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stevebauman\Location\Facades\Location;

class LoginAuditService
{
    public function record(array $payload, ?Request $request = null): ?LoginLog
    {
        if (! Schema::hasTable('login_logs')) {
            return null;
        }

        $request = $request ?: request();
        $ip = $payload['ip_address'] ?? $request?->ip();
        $geo = $this->resolveGeo($ip);

        $guard = (string) ($payload['guard'] ?? 'api');
        $isAdminBackend = (bool) ($payload['is_admin_backend']
            ?? in_array($guard, ['admin', 'admin-web'], true));

        $log = LoginLog::create([
            'guard' => $guard,
            'actor_type' => $payload['actor_type'] ?? 'unknown',
            'actor_id' => $payload['actor_id'] ?? null,
            'email' => isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : null,
            'successful' => (bool) ($payload['successful'] ?? false),
            'event' => $payload['event'] ?? (($payload['successful'] ?? false) ? 'login' : 'login_failed'),
            'failure_reason' => $payload['failure_reason'] ?? null,
            'ip_address' => $ip,
            'user_agent' => substr((string) ($payload['user_agent'] ?? $request?->userAgent() ?? ''), 0, 2000),
            'country' => $geo['country'],
            'region' => $geo['region'],
            'city' => $geo['city'],
            'latitude' => $geo['latitude'],
            'longitude' => $geo['longitude'],
            'location_label' => $geo['label'],
            'is_admin_backend' => $isAdminBackend,
            'meta' => $payload['meta'] ?? null,
        ]);

        try {
            $this->maybeAlert($log);
        } catch (\Throwable $e) {
            Log::warning('Login security alert failed: '.$e->getMessage());
        }

        return $log;
    }

    public function recordCustomerSuccess(string $email, $customerId, string $guard = 'api', string $event = 'login', array $meta = []): ?LoginLog
    {
        return $this->record([
            'guard' => $guard,
            'actor_type' => 'customer',
            'actor_id' => $customerId,
            'email' => $email,
            'successful' => true,
            'event' => $event,
            'is_admin_backend' => false,
            'meta' => $meta,
        ]);
    }

    public function recordCustomerFailure(string $email, string $reason, string $guard = 'api', string $event = 'login_failed', array $meta = []): ?LoginLog
    {
        return $this->record([
            'guard' => $guard,
            'actor_type' => 'customer',
            'email' => $email,
            'successful' => false,
            'event' => $event,
            'failure_reason' => $reason,
            'is_admin_backend' => false,
            'meta' => $meta,
        ]);
    }

    public function recordAdminSuccess(string $email, $userId, string $guard = 'admin-web', string $event = 'login', array $meta = []): ?LoginLog
    {
        return $this->record([
            'guard' => $guard,
            'actor_type' => 'user',
            'actor_id' => $userId,
            'email' => $email,
            'successful' => true,
            'event' => $event,
            'is_admin_backend' => true,
            'meta' => $meta,
        ]);
    }

    public function recordAdminFailure(string $email, string $reason, string $guard = 'admin-web', string $event = 'login_failed', array $meta = []): ?LoginLog
    {
        return $this->record([
            'guard' => $guard,
            'actor_type' => 'user',
            'email' => $email,
            'successful' => false,
            'event' => $event,
            'failure_reason' => $reason,
            'is_admin_backend' => true,
            'meta' => $meta,
        ]);
    }

    protected function resolveGeo(?string $ip): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'label' => null,
        ];

        if (! $ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return array_merge($empty, ['label' => $ip ? 'Local / private IP' : null]);
        }

        try {
            $position = Location::get($ip);
            if (! $position) {
                return array_merge($empty, ['label' => $ip]);
            }

            $country = $position->countryName ?: null;
            $region = $position->regionName ?: null;
            $city = $position->cityName ?: null;
            $parts = array_filter([$city, $region, $country]);

            return [
                'country' => $country,
                'region' => $region,
                'city' => $city,
                'latitude' => $position->latitude ? (string) $position->latitude : null,
                'longitude' => $position->longitude ? (string) $position->longitude : null,
                'label' => $parts ? implode(', ', $parts) : $ip,
            ];
        } catch (\Throwable $e) {
            Log::debug('Login geo lookup failed: '.$e->getMessage());

            return array_merge($empty, ['label' => $ip]);
        }
    }

    protected function maybeAlert(LoginLog $log): void
    {
        if (! config('security.login_alerts_enabled', true)) {
            return;
        }

        $shouldAlert = false;
        $severity = 'medium';

        if ($log->is_admin_backend && config('security.alert_all_admin_logins', true)) {
            $shouldAlert = true;
            $severity = $log->successful ? 'high' : 'critical';
        }

        if (! $log->successful && ! $log->is_admin_backend) {
            $window = (int) config('security.failed_attempt_window_minutes', 15);
            $threshold = (int) config('security.failed_attempt_threshold', 3);

            $recentFails = LoginLog::query()
                ->where('successful', false)
                ->where('created_at', '>=', now()->subMinutes($window))
                ->where(function ($q) use ($log) {
                    if ($log->email) {
                        $q->where('email', $log->email);
                    }
                    if ($log->ip_address) {
                        $q->orWhere('ip_address', $log->ip_address);
                    }
                })
                ->count();

            if ($recentFails >= $threshold) {
                $shouldAlert = true;
                $severity = 'high';
            }
        }

        if ($log->successful && ! $log->is_admin_backend && $log->actor_id && $log->ip_address) {
            $seenIp = LoginLog::query()
                ->where('actor_type', 'customer')
                ->where('actor_id', $log->actor_id)
                ->where('successful', true)
                ->where('ip_address', $log->ip_address)
                ->where('id', '!=', $log->id)
                ->exists();

            if (! $seenIp) {
                $shouldAlert = true;
            }
        }

        if (! $shouldAlert) {
            return;
        }

        $status = $log->successful ? 'SUCCESS' : 'FAILED';
        $who = $log->email ?: ('#'.$log->actor_id);
        $where = $log->location_label ?: ($log->ip_address ?: 'unknown location');
        $message = sprintf(
            '[%s] %s login %s — %s from %s (IP %s, guard %s)',
            strtoupper($severity),
            $log->is_admin_backend ? 'ADMIN BACKEND' : 'User',
            $status,
            $who,
            $where,
            $log->ip_address ?: 'n/a',
            $log->guard
        );

        $data = [
            'login_log_id' => $log->id,
            'severity' => $severity,
            'email' => $log->email,
            'ip' => $log->ip_address,
            'location' => $log->location_label,
            'country' => $log->country,
            'city' => $log->city,
            'guard' => $log->guard,
            'successful' => $log->successful,
            'event' => $log->event,
            'is_admin_backend' => $log->is_admin_backend,
            'user_agent' => $log->user_agent,
        ];

        $this->notifySecurityStaff($message, $data);
        MailHelper::sendSecurityLoginAlert($message, $data);
        $log->update(['alerted' => true]);
    }

    protected function notifySecurityStaff(string $message, array $data): void
    {
        $table = (new AdminNotification)->getTable();
        if (! Schema::hasTable($table)) {
            return;
        }

        $users = User::query()
            ->where(function ($q) {
                $q->where('is_super_admin', true);
                if (Schema::hasColumn('users', 'can_view_security_logs')) {
                    $q->orWhere('can_view_security_logs', true);
                }
            })
            ->get();

        if ($users->isEmpty()) {
            AdminNotification::notifyAllAdmins(AdminNotification::TYPE_SYSTEM_ALERT, $message, $data);

            return;
        }

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                'user_id' => $user->user_id,
                'type' => AdminNotification::TYPE_SYSTEM_ALERT,
                'message' => $message,
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        AdminNotification::insert($rows);
    }
}
