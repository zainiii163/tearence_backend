<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\Service;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = ServiceOrder::with(['service', 'buyer', 'seller'])
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function getSellerOrders(Request $request)
    {
        $orders = ServiceOrder::where('seller_id', auth()->id())
            ->with(['service', 'buyer'])
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function getBuyerOrders(Request $request)
    {
        $orders = ServiceOrder::where('buyer_id', auth()->id())
            ->with(['service', 'seller'])
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function getOrderStats()
    {
        $stats = [
            'total_orders' => ServiceOrder::count(),
            'pending_orders' => ServiceOrder::where('status', 'pending')->count(),
            'completed_orders' => ServiceOrder::where('status', 'completed')->count(),
            'cancelled_orders' => ServiceOrder::where('status', 'cancelled')->count(),
        ];

        return response()->json($stats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'requirements' => 'required|string',
        ]);

        $service = Service::findOrFail($request->service_id);
        
        $order = ServiceOrder::create([
            'service_id' => $service->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $service->user_id,
            'requirements' => $request->requirements,
            'amount' => $service->price,
            'status' => 'pending',
        ]);

        return response()->json($order, 201);
    }

    public function show(ServiceOrder $order)
    {
        $order->load(['service', 'buyer', 'seller']);
        return response()->json($order);
    }

    public function updateStatus(Request $request, ServiceOrder $order)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,rejected,completed,cancelled',
        ]);

        $order->update(['status' => $request->status]);
        return response()->json($order);
    }

    public function acceptOrder(ServiceOrder $order)
    {
        if ($order->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->update(['status' => 'accepted']);
        return response()->json($order);
    }

    public function rejectOrder(ServiceOrder $order)
    {
        if ($order->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->update(['status' => 'rejected']);
        return response()->json($order);
    }

    public function completeOrder(ServiceOrder $order)
    {
        if ($order->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->update(['status' => 'completed']);
        return response()->json($order);
    }

    public function requestRefund(ServiceOrder $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->update(['status' => 'cancelled']);
        return response()->json($order);
    }

    public function addReview(Request $request, ServiceOrder $order)
    {
        if ($order->buyer_id !== auth()->id() || $order->status !== 'completed') {
            return response()->json(['error' => 'Unauthorized or order not completed'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);

        $order->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json($order);
    }
}
