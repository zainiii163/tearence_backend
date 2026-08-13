<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Models\BusinessTool;
use App\Models\BusinessToolPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class BusinessToolController extends Controller
{
    use VerifiesClientPayments;
    public function index(Request $request): JsonResponse
    {
        try {
            if (!Schema::hasTable('business_tools')) {
                return response()->json([
                    'success' => true,
                    'data' => ['data' => [], 'total' => 0],
                    'message' => 'Run migrations and BusinessToolSeeder.',
                ]);
            }

            $query = BusinessTool::query()->active();

            if ($request->filled('tag')) {
                $query->where('tag', $request->tag);
            }
            if ($request->filled('category_slug')) {
                $query->where(function ($q) use ($request) {
                    $q->whereNull('category_slug')
                        ->orWhere('category_slug', $request->category_slug);
                });
            }
            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('blurb', 'like', "%{$term}%");
                });
            }

            $query->orderBy('sort_order')->orderBy('title');
            $perPage = min((int) ($request->per_page ?? 24), 50);
            $items = $query->paginate($perPage);

            return response()->json(['success' => true, 'data' => $items]);
        } catch (\Throwable $e) {
            Log::error('BusinessTool index: '.$e->getMessage());

            return response()->json([
                'success' => true,
                'data' => ['data' => [], 'total' => 0],
                'warning' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    public function show(string $slug): JsonResponse
    {
        $tool = BusinessTool::where('slug', $slug)->active()->first();
        if (!$tool) {
            return response()->json(['success' => false, 'message' => 'Tool not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $tool]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $user = Auth::user() ?? auth('api')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'tool_id' => 'nullable|integer',
            'slug' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $tool = null;
        if ($request->filled('tool_id')) {
            $tool = BusinessTool::where('id', $request->tool_id)->active()->first();
        } elseif ($request->filled('slug')) {
            $tool = BusinessTool::where('slug', $request->slug)->active()->first();
        }
        if (!$tool) {
            return response()->json(['success' => false, 'message' => 'Tool not found.'], 404);
        }

        $customerId = $user->customer_id ?? $user->id;
        $existing = BusinessToolPurchase::where('tool_id', $tool->id)
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already purchased.',
                'data' => $existing->load('tool'),
            ]);
        }

        $purchase = BusinessToolPurchase::create([
            'tool_id' => $tool->id,
            'customer_id' => $customerId,
            'amount' => $tool->price,
            'currency' => $tool->currency ?: 'USD',
            'payment_method' => $request->input('payment_method', 'manual'),
            'status' => 'pending',
            'download_token' => BusinessToolPurchase::mintToken(),
        ]);

        if ((float) $tool->price <= 0) {
            $purchase->update(['status' => 'paid', 'paid_at' => now()]);
            $tool->increment('purchases_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase created.',
            'data' => $purchase->fresh()->load('tool'),
            'needs_payment' => $purchase->status === 'pending',
        ], 201);
    }

    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $user = Auth::user() ?? auth('api')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $customerId = $user->customer_id ?? $user->id;
        $purchase = BusinessToolPurchase::where('id', $id)
            ->where('customer_id', $customerId)
            ->first();
        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase not found.'], 404);
        }

        if ($purchase->status !== 'paid') {
            $expected = (float) ($purchase->amount ?? 0);
            if ($expected < 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase amount missing — cannot unlock without a paid order.',
                    'defence' => 'payment_verification',
                ], 422);
            }

            $request->validate([
                'payment_id' => 'nullable|string|max:191',
                'payment_reference' => 'nullable|string|max:191',
                'payment_method' => 'nullable|string|max:50',
            ]);

            $verified = $this->verifyClientPaymentOrFail(
                $request,
                $expected,
                'business_tool',
                $purchase->id
            );
            if ($verified instanceof JsonResponse) {
                return $verified;
            }

            $purchase->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => $verified['payment_id'],
                'payment_method' => $request->input('payment_method', $purchase->payment_method ?: 'paypal'),
            ]);
            BusinessTool::where('id', $purchase->tool_id)->increment('purchases_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Tool unlocked.',
            'data' => $purchase->fresh()->load('tool'),
        ]);
    }

    public function myPurchases(): JsonResponse
    {
        $user = Auth::user() ?? auth('api')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $customerId = $user->customer_id ?? $user->id;
        $items = BusinessToolPurchase::with('tool')
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
     }
}
