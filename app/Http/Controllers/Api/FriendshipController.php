<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CommunityAuthHelper;
use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FriendshipController extends Controller
{
    /**
     * Resolve the authenticated Social Hub user id (users.user_id).
     */
    protected function userId(): ?int
    {
        return CommunityAuthHelper::usersUserId(null, true);
    }

    protected function guardUser(?int $id)
    {
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Please log in to use friends.'], 401);
        }
        return null;
    }

    protected function userPayload($user): array
    {
        if (!$user) {
            return null;
        }
        return [
            'user_id' => $user->user_id,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'avatar' => $user->profile_photo_path ?? $user->avatar ?? null,
        ];
    }

    protected function decorate($rows, int $actorId): array
    {
        return collect($rows)->map(function ($row) use ($actorId) {
            $otherId = $row->other($actorId);
            $payload = [
                'friendship' => [
                    'id' => $row->id,
                    'status' => $row->status,
                    'is_incoming' => (int) $row->addressee_id === (int) $actorId,
                ],
                'user' => $this->userPayload(User::find($otherId)),
            ];
            return $payload;
        })->filter(fn ($r) => $r['user'] !== null)->values()->all();
    }

    /**
     * Send a friend request to another Social Hub user.
     */
    public function send(Request $request, $userId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        if ((int) $userId === (int) $actorId) {
            return response()->json(['success' => false, 'message' => 'You cannot befriend yourself.'], 422);
        }

        if (!User::where('user_id', $userId)->exists()) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $existing = Friendship::between($actorId, $userId);
        if ($existing) {
            if ($existing->status === Friendship::STATUS_ACCEPTED) {
                return response()->json(['success' => false, 'message' => 'You are already friends.'], 422);
            }
            if ($existing->status === Friendship::STATUS_PENDING) {
                return response()->json(['success' => false, 'message' => 'A friend request already exists.'], 422);
            }
            if ($existing->status === Friendship::STATUS_BLOCKED) {
                return response()->json(['success' => false, 'message' => 'Action not allowed.'], 403);
            }
            // Reuse the row (e.g. after a declined/cancelled state)
            $existing->update([
                'requester_id' => $actorId,
                'addressee_id' => $userId,
                'status' => Friendship::STATUS_PENDING,
                'responded_at' => null,
            ]);
            return response()->json(['success' => true, 'message' => 'Friend request sent.', 'data' => ['id' => $existing->id, 'status' => $existing->status]]);
        }

        $friendship = Friendship::create([
            'requester_id' => $actorId,
            'addressee_id' => $userId,
            'status' => Friendship::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Friend request sent.',
            'data' => ['id' => $friendship->id, 'status' => $friendship->status],
        ], 201);
    }

    /**
     * Accept an incoming friend request.
     */
    public function accept(Request $request, $friendshipId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $friendship = Friendship::findOrFail($friendshipId);
        if ((int) $friendship->addressee_id !== (int) $actorId) {
            return response()->json(['success' => false, 'message' => 'Only the recipient can accept this request.'], 403);
        }
        if ($friendship->status !== Friendship::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'This request is no longer pending.'], 422);
        }

        $friendship->update([
            'status' => Friendship::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Friend request accepted.', 'data' => ['id' => $friendship->id, 'status' => $friendship->status]]);
    }

    /**
     * Decline an incoming friend request.
     */
    public function decline(Request $request, $friendshipId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $friendship = Friendship::findOrFail($friendshipId);
        if ((int) $friendship->addressee_id !== (int) $actorId) {
            return response()->json(['success' => false, 'message' => 'Only the recipient can decline this request.'], 403);
        }
        if ($friendship->status !== Friendship::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'This request is no longer pending.'], 422);
        }

        $friendship->update([
            'status' => Friendship::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Friend request declined.', 'data' => ['id' => $friendship->id, 'status' => $friendship->status]]);
    }

    /**
     * Cancel a friend request the user sent.
     */
    public function cancel(Request $request, $friendshipId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $friendship = Friendship::findOrFail($friendshipId);
        if ((int) $friendship->requester_id !== (int) $actorId) {
            return response()->json(['success' => false, 'message' => 'Only the requester can cancel this request.'], 403);
        }

        $friendship->update(['status' => Friendship::STATUS_CANCELLED]);

        return response()->json(['success' => true, 'message' => 'Friend request cancelled.', 'data' => ['id' => $friendship->id, 'status' => $friendship->status]]);
    }

    /**
     * Remove a friend (break an accepted friendship).
     */
    public function remove(Request $request, $friendshipId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $friendship = Friendship::findOrFail($friendshipId);
        if (!$friendship->involves($actorId)) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $friendship->update(['status' => Friendship::STATUS_CANCELLED]);

        return response()->json(['success' => true, 'message' => 'Friendship removed.', 'data' => ['id' => $friendship->id, 'status' => $friendship->status]]);
    }

    /**
     * List accepted friends.
     */
    public function friends(Request $request)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $rows = Friendship::where('status', Friendship::STATUS_ACCEPTED)
            ->where(function ($q) use ($actorId) {
                $q->where('requester_id', $actorId)->orWhere('addressee_id', $actorId);
            })
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['success' => true, 'data' => $this->decorate($rows, $actorId)]);
    }

    /**
     * List incoming pending friend requests.
     */
    public function incoming(Request $request)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $rows = Friendship::where('addressee_id', $actorId)
            ->where('status', Friendship::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $this->decorate($rows, $actorId)]);
    }

    /**
     * List outgoing pending friend requests.
     */
    public function outgoing(Request $request)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $rows = Friendship::where('requester_id', $actorId)
            ->where('status', Friendship::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $this->decorate($rows, $actorId)]);
    }

    /**
     * Return friendship status between the current user and another user.
     */
    public function status(Request $request, $userId)
    {
        $actorId = $this->userId();
        if ($gate = $this->guardUser($actorId)) {
            return $gate;
        }

        $friendship = Friendship::between($actorId, $userId);
        if (!$friendship) {
            return response()->json(['success' => true, 'status' => 'none', 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'status' => $friendship->status,
            'is_incoming' => (int) $friendship->addressee_id === (int) $actorId,
            'data' => ['id' => $friendship->id, 'status' => $friendship->status],
        ]);
    }
}
