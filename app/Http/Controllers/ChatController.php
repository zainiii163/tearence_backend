<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\CustomerNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function authCustomerId(): ?int
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return (int) ($user instanceof Customer ? $user->customer_id : $user->id);
    }

    private function ok($data, string $message = 'OK', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'status' => 'Success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function fail(string $message, int $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'status' => 'Error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function formatParticipant(?Customer $customer): array
    {
        if (! $customer) {
            return [
                'id' => null,
                'name' => 'User',
                'avatar' => null,
            ];
        }

        return [
            'id' => $customer->customer_id,
            'name' => trim((string) ($customer->name ?: ($customer->first_name.' '.$customer->last_name))) ?: 'User',
            'avatar' => $customer->avatar ?? null,
        ];
    }

    private function formatConversation(Conversation $conversation, int $viewerId): array
    {
        $otherId = $conversation->otherParticipantId($viewerId);
        $other = $otherId === (int) $conversation->seller_id
            ? $conversation->seller
            : $conversation->buyer;

        $last = $conversation->relationLoaded('lastMessage')
            ? $conversation->lastMessage
            : $conversation->messages()->latest('id')->first();

        $unread = $conversation->messages()
            ->where('sender_id', '!=', $viewerId)
            ->whereNull('read_at')
            ->count();

        $listing = null;
        if ($conversation->listing_id || $conversation->listing_title) {
            $listing = [
                'listing_id' => $conversation->listing_id,
                'title' => $conversation->listing_title ?: 'Listing',
                'type' => $conversation->listing_type,
            ];
        }

        return [
            'conversation_id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'listing' => $listing,
            'listing_type' => $conversation->listing_type,
            'other_participant' => $this->formatParticipant($other),
            'last_message' => $last ? [
                'message_id' => $last->id,
                'message' => $last->message,
                'message_type' => $last->message_type,
                'sender_id' => $last->sender_id,
                'created_at' => optional($last->created_at)->toIso8601String(),
            ] : null,
            'last_message_at' => optional($conversation->last_message_at ?: optional($last)->created_at)->toIso8601String(),
            'unread_count' => $unread,
            'created_at' => optional($conversation->created_at)->toIso8601String(),
        ];
    }

    private function formatMessage(ChatMessage $message, int $viewerId): array
    {
        return [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'message' => $message->message,
            'message_type' => $message->message_type ?: 'text',
            'is_own_message' => (int) $message->sender_id === $viewerId,
            'read_at' => optional($message->read_at)->toIso8601String(),
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }

    /**
     * GET /chat/conversations
     */
    public function getConversations(Request $request)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $conversations = Conversation::query()
                ->with(['buyer', 'seller', 'lastMessage'])
                ->where(function ($q) use ($userId) {
                    $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
                })
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (Conversation $c) => $this->formatConversation($c, $userId))
                ->values();

            return $this->ok($conversations, 'Conversations retrieved successfully');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /chat/conversations
     * Body: seller_id, listing_id?, listing_type?/category?, subject?, initial_message
     */
    public function startConversation(Request $request)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            // Clients often send numeric listing IDs as JSON numbers; DB stores string (UUID-capable).
            $input = $request->all();
            if (array_key_exists('listing_id', $input) && $input['listing_id'] !== null && $input['listing_id'] !== '') {
                $input['listing_id'] = (string) $input['listing_id'];
            }

            $validator = Validator::make($input, [
                'seller_id' => 'required',
                'listing_id' => 'nullable|string|max:64',
                'listing_type' => 'nullable|string|max:32',
                'category' => 'nullable|string|max:64',
                'listing_title' => 'nullable|string|max:255',
                'subject' => 'nullable|string|max:255',
                'initial_message' => 'required|string|min:1|max:5000',
            ]);

            if ($validator->fails()) {
                return $this->fail($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $sellerId = (int) $request->input('seller_id');
            if ($sellerId <= 0) {
                return $this->fail('Invalid seller_id', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($sellerId === $userId) {
                return $this->fail('You cannot start a chat with yourself', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $seller = Customer::query()->where('customer_id', $sellerId)->first();
            if (! $seller) {
                return $this->fail('Seller not found', Response::HTTP_NOT_FOUND);
            }

            $listingType = $request->input('listing_type') ?: $request->input('category');
            if (is_string($listingType)) {
                $listingType = strtolower(trim(str_replace([' & ', ' '], ['-', '-'], $listingType)));
            }
            $listingId = $request->input('listing_id');
            $listingId = $listingId !== null && $listingId !== '' ? (string) $listingId : null;
            $listingKey = Conversation::makeListingKey($listingType, $listingId);
            $listingTitle = $request->input('listing_title');
            $subject = $request->input('subject') ?: (
                $listingTitle
                    ? 'Inquiry about: '.$listingTitle
                    : 'General Inquiry'
            );

            $conversation = DB::transaction(function () use (
                $userId,
                $sellerId,
                $listingId,
                $listingType,
                $listingKey,
                $listingTitle,
                $subject,
                $request
            ) {
                $conversation = Conversation::query()
                    ->where('buyer_id', $userId)
                    ->where('seller_id', $sellerId)
                    ->where('listing_key', $listingKey)
                    ->first();

                if (! $conversation) {
                    $conversation = Conversation::create([
                        'buyer_id' => $userId,
                        'seller_id' => $sellerId,
                        'listing_id' => $listingId,
                        'listing_type' => $listingType,
                        'listing_key' => $listingKey,
                        'listing_title' => $listingTitle,
                        'subject' => $subject,
                        'status' => 'open',
                        'last_message_at' => now(),
                    ]);
                } else {
                    $conversation->update([
                        'status' => 'open',
                        'subject' => $conversation->subject ?: $subject,
                        'listing_title' => $conversation->listing_title ?: $listingTitle,
                        'last_message_at' => now(),
                    ]);
                }

                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $userId,
                    'message' => trim((string) $request->input('initial_message')),
                    'message_type' => 'text',
                ]);

                return $conversation->fresh(['buyer', 'seller', 'lastMessage']);
            });

            try {
                $buyer = Customer::query()->where('customer_id', $userId)->first();
                $buyerName = $buyer ? trim((string) $buyer->name) : 'A buyer';
                $about = $conversation->listing_title
                    ? ' about "'.$conversation->listing_title.'"'
                    : '';
                CustomerNotification::notify(
                    $sellerId,
                    CustomerNotification::TYPE_MESSAGE,
                    "{$buyerName} started a live chat{$about}",
                    'New live chat message',
                    [
                        'conversation_id' => $conversation->id,
                        'listing_id' => $conversation->listing_id,
                        'listing_type' => $conversation->listing_type,
                        'url' => '/messages?c='.$conversation->id,
                    ]
                );
            } catch (\Throwable $e) {
                // Non-fatal
            }

            return $this->ok([
                'conversation_id' => $conversation->id,
                'conversation' => $this->formatConversation($conversation, $userId),
            ], 'Conversation started', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /chat/conversations/{id}/messages
     */
    public function getMessages(Request $request, $id)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $conversation = Conversation::query()->find($id);
            if (! $conversation || ! $conversation->involvesUser($userId)) {
                return $this->fail('Conversation not found', Response::HTTP_NOT_FOUND);
            }

            $perPage = min(100, max(1, (int) $request->input('per_page', 50)));
            $page = max(1, (int) $request->input('page', 1));

            $query = ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->orderBy('id');

            $total = (clone $query)->count();
            $messages = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(fn (ChatMessage $m) => $this->formatMessage($m, $userId))
                ->values();

            // Mark incoming messages as read
            ChatMessage::query()
                ->where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $this->ok([
                'messages' => $messages,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                ],
            ], 'Messages retrieved successfully');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /chat/conversations/{id}/messages
     */
    public function sendMessage(Request $request, $id)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|min:1|max:5000',
                'message_type' => 'nullable|string|max:32',
            ]);

            if ($validator->fails()) {
                return $this->fail($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            }

            $conversation = Conversation::query()->find($id);
            if (! $conversation || ! $conversation->involvesUser($userId)) {
                return $this->fail('Conversation not found', Response::HTTP_NOT_FOUND);
            }

            if ($conversation->status === 'closed') {
                $conversation->update(['status' => 'open']);
            }

            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'message' => trim((string) $request->input('message')),
                'message_type' => $request->input('message_type', 'text') ?: 'text',
            ]);

            $conversation->update(['last_message_at' => now()]);

            $recipientId = $conversation->otherParticipantId($userId);
            try {
                $sender = Customer::query()->where('customer_id', $userId)->first();
                $senderName = $sender ? trim((string) $sender->name) : 'Someone';
                CustomerNotification::notify(
                    $recipientId,
                    CustomerNotification::TYPE_MESSAGE,
                    "{$senderName}: ".mb_strimwidth($message->message, 0, 120, '…'),
                    'New live chat message',
                    [
                        'conversation_id' => $conversation->id,
                        'message_id' => $message->id,
                        'url' => '/messages?c='.$conversation->id,
                    ]
                );
            } catch (\Throwable $e) {
                // Non-fatal
            }

            return $this->ok(
                $this->formatMessage($message, $userId),
                'Message sent',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT /chat/conversations/{id}/close
     */
    public function closeConversation(Request $request, $id)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $conversation = Conversation::query()->find($id);
            if (! $conversation || ! $conversation->involvesUser($userId)) {
                return $this->fail('Conversation not found', Response::HTTP_NOT_FOUND);
            }

            $conversation->update(['status' => 'closed']);

            return $this->ok([
                'conversation_id' => $conversation->id,
                'status' => 'closed',
            ], 'Conversation closed');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /chat/unread-count
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $userId = $this->authCustomerId();
            if (! $userId) {
                return $this->fail('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $conversationIds = Conversation::query()
                ->where(function ($q) use ($userId) {
                    $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
                })
                ->pluck('id');

            $unreadCount = 0;
            if ($conversationIds->isNotEmpty()) {
                $unreadCount = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->where('sender_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count();
            }

            return $this->ok([
                'unread_count' => $unreadCount,
            ], 'Unread count retrieved successfully');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
