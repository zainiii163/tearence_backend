<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\ServicePackage;
use App\Models\ServiceReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrder::with(['service', 'buyer', 'seller', 'package']);

        // Filter by user (buyer or seller)
        if ($request->type === 'buyer') {
            $query->where('buyer_id', Auth::id());
        } elseif ($request->type === 'seller') {
            $query->where('seller_id', Auth::id());
        }

        // Filter by status
        if ($request->status) {
            $query->byStatus($request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'package_id' => 'nullable|exists:service_packages,id',
            'requirements' => 'required|array',
            'buyer_notes' => 'nullable|string',
        ]);

        $service = Service::findOrFail($request->service_id);
        
        if ($service->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot order your own service',
            ], 400);
        }

        $package = null;
        $totalPrice = $service->base_price;

        if ($request->package_id) {
            $package = ServicePackage::findOrFail($request->package_id);
            if ($package->service_id !== $service->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid package for this service',
                ], 400);
            }
            $totalPrice = $package->price;
        }

        DB::beginTransaction();
        try {
            $order = ServiceOrder::create([
                'service_id' => $service->id,
                'buyer_id' => Auth::id(),
                'seller_id' => $service->user_id,
                'package_id' => $request->package_id,
                'requirements' => $request->requirements,
                'total_price' => $totalPrice,
                'delivery_time' => $package->delivery_time ?? $service->delivery_time,
                'status' => 'pending',
                'buyer_notes' => $request->buyer_notes,
            ]);

            // Increment service orders count
            $service->incrementOrders();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => $order->load(['service', 'buyer', 'seller', 'package']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ServiceOrder $order): JsonResponse
    {
        if ($order->buyer_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load(['service', 'buyer', 'seller', 'package', 'review']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only seller can update order status',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'seller_notes' => 'nullable|string',
        ]);

        $order->status = $request->status;
        $order->seller_notes = $request->seller_notes;

        if ($request->status === 'completed') {
            $order->completed_at = now();
        } elseif ($request->status === 'cancelled') {
            $order->cancelled_at = now();
        }

        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }

    public function acceptOrder(ServiceOrder $order): JsonResponse
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be accepted',
            ], 400);
        }

        $order->status = 'in_progress';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully',
            'data' => $order,
        ]);
    }

    public function rejectOrder(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be rejected',
            ], 400);
        }

        $request->validate([
            'seller_notes' => 'required|string',
        ]);

        $order->status = 'cancelled';
        $order->seller_notes = $request->seller_notes;
        $order->cancelled_at = now();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order rejected successfully',
            'data' => $order,
        ]);
    }

    public function completeOrder(ServiceOrder $order): JsonResponse
    {
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be completed',
            ], 400);
        }

        $order->status = 'completed';
        $order->completed_at = now();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order completed successfully',
            'data' => $order,
        ]);
    }

    public function requestRefund(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($order->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($order->status, ['pending', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be refunded',
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string',
        ]);

        $order->status = 'refunded';
        $order->refund_amount = $order->total_price;
        $order->buyer_notes = $request->reason;
        $order->cancelled_at = now();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Refund requested successfully',
            'data' => $order,
        ]);
    }

    public function addReview(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($order->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$order->canBeReviewed()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be reviewed',
            ], 400);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $review = ServiceReview::create([
            'service_id' => $order->service_id,
            'order_id' => $order->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $order->seller_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update service rating
        $order->service->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'data' => $review,
        ], 201);
    }

    public function getSellerOrders(Request $request): JsonResponse
    {
        $orders = ServiceOrder::with(['service', 'buyer', 'package'])
            ->where('seller_id', Auth::id())
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function getBuyerOrders(Request $request): JsonResponse
    {
        $orders = ServiceOrder::with(['service', 'seller', 'package'])
            ->where('buyer_id', Auth::id())
            ->when($request->status, function ($query, $status) {
                $query->byStatus($status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function getOrderStats(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total_orders' => ServiceOrder::where('seller_id', $userId)->count(),
            'pending_orders' => ServiceOrder::where('seller_id', $userId)->byStatus('pending')->count(),
            'in_progress_orders' => ServiceOrder::where('seller_id', $userId)->byStatus('in_progress')->count(),
            'completed_orders' => ServiceOrder::where('seller_id', $userId)->byStatus('completed')->count(),
            'cancelled_orders' => ServiceOrder::where('seller_id', $userId)->byStatus('cancelled')->count(),
            'total_earnings' => ServiceOrder::where('seller_id', $userId)->byStatus('completed')->sum('total_price'),
            'pending_earnings' => ServiceOrder::where('seller_id', $userId)->byStatus('in_progress')->sum('total_price'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
