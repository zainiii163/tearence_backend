<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\LoginAuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class RecordAuthLoginEvents
{
    public function handleLogin(Login $event): void
    {
        if (! $this->isAdminWebGuard($event->guard)) {
            return;
        }

        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        app(LoginAuditService::class)->recordAdminSuccess(
            (string) $user->email,
            $user->user_id,
            'admin-web',
            'login',
            ['source' => 'filament_session']
        );
    }

    public function handleFailed(Failed $event): void
    {
        if (! $this->isAdminWebGuard($event->guard)) {
            return;
        }

        $email = (string) ($event->credentials['email'] ?? '');
        app(LoginAuditService::class)->recordAdminFailure(
            $email,
            'invalid_credentials',
            'admin-web',
            'login_failed',
            ['source' => 'filament_session']
        );
    }

    public function handleLogout(Logout $event): void
    {
        if (! $this->isAdminWebGuard($event->guard)) {
            return;
        }

        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        app(LoginAuditService::class)->record([
            'guard' => 'admin-web',
            'actor_type' => 'user',
            'actor_id' => $user->user_id,
            'email' => $user->email,
            'successful' => true,
            'event' => 'logout',
            'is_admin_backend' => true,
            'meta' => ['source' => 'filament_session'],
        ]);
    }

    protected function isAdminWebGuard(?string $guard): bool
    {
        return in_array($guard, ['admin-web', 'admin'], true);
    }
}
