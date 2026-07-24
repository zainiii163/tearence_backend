<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function customerId(): int
    {
        $user = auth('api')->user();
        return (int) ($user->customer_id ?? $user->id);
    }

    public function index(Request $request)
    {
        $query = CustomerNotification::where('customer_id', $this->customerId());

        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $perPage = min((int) $request->get('per_page', 20), 50);
        $notifications = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => CustomerNotification::unreadCount($this->customerId()),
            ],
        ]);
    }

    public function markAsRead($id)
    {
        $notification = CustomerNotification::where('customer_id', $this->customerId())
            ->where('id', $id)
            ->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    public function markAllAsRead()
    {
        CustomerNotification::markAllRead($this->customerId());

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function markMultipleAsRead(Request $request)
    {
        $ids = $request->input('notification_ids', []);
        CustomerNotification::where('customer_id', $this->customerId())
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read',
        ]);
    }

    public function destroy($id)
    {
        CustomerNotification::where('customer_id', $this->customerId())
            ->where('id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    public function destroyAll()
    {
        CustomerNotification::where('customer_id', $this->customerId())->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted',
        ]);
    }

    public function settings()
    {
        $user = auth('api')->user();
        $prefs = $user->notification_prefs ?? [];

        return response()->json([
            'success' => true,
            'data' => array_merge([
                'advert_expiry' => true,
                'promotion_ending' => true,
                'subscription' => true,
                'admin' => true,
                'messages' => true,
                'sales' => true,
                'email' => true,
            ], is_array($prefs) ? $prefs : []),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = auth('api')->user();
        $allowed = [
            'advert_expiry', 'promotion_ending', 'subscription',
            'admin', 'messages', 'sales', 'email',
        ];
        $prefs = $user->notification_prefs ?? [];
        if (!is_array($prefs)) {
            $prefs = [];
        }

        foreach ($allowed as $key) {
            if ($request->has($key)) {
                $prefs[$key] = (bool) $request->input($key);
            }
        }

        $user->notification_prefs = $prefs;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated',
            'data' => $prefs,
        ]);
    }
}
