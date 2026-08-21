<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Community / Social Hub tables FK to users.user_id, but the public API
 * authenticates as Customer (customer_id). Map the current actor to a users row.
 */
class CommunityAuthHelper
{
    public static function authUser()
    {
        return auth('api')->user() ?: auth()->user();
    }

    /**
     * Resolve users.user_id for the given (or current) auth actor.
     * Creates a linked users row by email when the actor is a Customer with no match.
     */
    public static function usersUserId($user = null, bool $createIfMissing = true): ?int
    {
        $user = $user ?? self::authUser();
        if (!$user) {
            return null;
        }

        if ($user instanceof User) {
            $id = $user->user_id ?? $user->getKey();
            return $id !== null ? (int) $id : null;
        }

        $email = $user->email ?? null;
        if (!$email) {
            return null;
        }

        $existing = User::where('email', $email)->value('user_id');
        if ($existing) {
            return (int) $existing;
        }

        if (!$createIfMissing) {
            return null;
        }

        $linked = User::create([
            'first_name' => $user->first_name ?: 'Member',
            'last_name' => $user->last_name ?: '',
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'user_uid' => Str::random(13),
            'timezone' => $user->timezone ?? 'UTC',
            'email_verified_at' => $user->email_verified_at ?? null,
        ]);

        return (int) $linked->user_id;
    }

    public static function usersUser($user = null, bool $createIfMissing = true): ?User
    {
        $id = self::usersUserId($user, $createIfMissing);
        return $id ? User::find($id) : null;
    }
}
