<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlatformFeeHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RecordsCategoryMoneyFlow;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Models\StoreOrder;
use App\Models\StoreProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class StoreOrderController extends Controller
{
    use VerifiesClientPayments;
    use RecordsCategoryMoneyFlow;

    /** Demo catalogue for the Worldwide Adverts example storefront. */
    public static function demoProducts(): array
    {
        return [
            [
                'id' => 'demo-1',
                'slug' => 'hand-loom-throw',
                'title' => 'Hand-loom throw',
                'description' => 'Soft woven throw in ocean teal — ships worldwide.',
                'price' => 48.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e6?auto=format&fit=crop&w=800&q=80',
                'category' => 'home',
            ],
            [
                'id' => 'demo-2',
                'slug' => 'ceramic-pour-over',
                'title' => 'Ceramic pour-over set',
                'description' => 'Stoneware dripper and cup for daily ritual coffee.',
                'price' => 36.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1493106641515-6ad53afa4dc6?auto=format&fit=crop&w=800&q=80',
                'category' => 'kitchen',
            ],
            [
                'id' => 'demo-3',
                'slug' => 'walnut-desk-tray',
                'title' => 'Walnut desk tray',
                'description' => 'Solid walnut organiser for keys, cards and pens.',
                'price' => 62.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&w=800&q=80',
                'category' => 'office',
            ],
            [
                'id' => 'demo-4',
                'slug' => 'linen-apron',
                'title' => 'Linen apron',
                'description' => 'Washed linen apron with deep pockets for makers.',
                'price' => 29.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?auto=format&fit=crop&w=800&q=80',
                'category' => 'apparel',
            ],
            [
                'id' => 'demo-5',
                'slug' => 'brass-candle-set',
                'title' => 'Brass candle set',
                'description' => 'Pair of brushed brass holders with beeswax tapers.',
                'price' => 54.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1602874801006-e26c4c6b0c0a?auto=format&fit=crop&w=800&q=80',
                'category' => 'home',
            ],
            [
                'id' => 'demo-6',
                'slug' => 'travel-journal',
                'title' => 'Travel journal',
                'description' => 'Cloth-bound notebook with map endpapers.',
                'price' => 22.00,
                'currency' => 'USD',
                'image_url' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=800&q=80',
                'category' => 'stationery',
            ],
        ];
    }

    public function exampleCatalogue(): JsonResponse
    {
        $feePercent = PlatformFeeHelper::percent();
        $products = [];

        if (Schema::hasTable('store_products')) {
            $rows = StoreProduct::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(24)
                ->get();
            foreach ($rows as $row) {
                $products[] = [
                    'id' => $row->id,
                    'slug' => $row->slug,
                    'title' => $row->title,
                    'description' => $row->description,
                    'price' => (float) $row->price,
                    'currency' => $row->currency ?: 'USD',
                    'image_url' => $row->image_url,
                    'category' => $row->category,
                ];
            }
        }

        if (! $products) {
            $products = self::demoProducts();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'store' => [
                    'slug' => 'wwa-atelier',
                    'name' => 'WWA Atelier',
                    'tagline' => 'Curated goods from makers on Worldwide Adverts',
                    'fee_percent' => $feePercent,
                ],
                'products' => $products,
                'fee_percent' => $feePercent,
            ],
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        if (! Schema::hasTable('store_orders')) {
            return response()->json(['success' => false, 'message' => 'Store checkout not available yet'], 503);
        }

        $request->validate([
            'product_id' => 'nullable',
            'product_slug' => 'nullable|string|max:191',
            'title' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0.5',
        ]);

        $buyerId = Auth::guard('api')->id() ?: Auth::id();
        if (! $buyerId) {
            return response()->json(['success' => false, 'message' => 'Login required'], 401);
        }

        $product = null;
        if (Schema::hasTable('store_products')) {
            if ($request->filled('product_id') && is_numeric($request->input('product_id'))) {
                $product = StoreProduct::find($request->input('product_id'));
            } elseif ($request->filled('product_slug')) {
                $product = StoreProduct::where('slug', $request->input('product_slug'))->first();
            }
        }

        $demo = collect(self::demoProducts())->first(function ($p) use ($request) {
            return ($request->filled('product_slug') && $p['slug'] === $request->input('product_slug'))
                || ($request->filled('product_id') && (string) $p['id'] === (string) $request->input('product_id'));
        });

        $title = $product?->title ?: ($demo['title'] ?? $request->input('title') ?: 'Store product');
        $amount = $product ? (float) $product->price : (float) ($demo['price'] ?? $request->input('amount', 0));
        $currency = $product?->currency ?: ($demo['currency'] ?? 'USD');

        if ($amount < 0.5) {
            return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
        }

        $fee = PlatformFeeHelper::split($amount);

        $order = StoreOrder::create([
            'store_product_id' => is_numeric($product?->id) ? $product->id : null,
            'store_id' => $product?->store_id,
            'buyer_id' => $buyerId,
            'seller_id' => $product?->seller_id,
            'title' => $title,
            'amount' => $amount,
            'currency' => $currency,
            'fee_percent' => $fee['fee_percent'],
            'platform_fee' => $fee['platform_fee'],
            'seller_amount' => $fee['seller_amount'],
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order created. Complete PayPal payment to finish.',
            'data' => [
                'order_id' => $order->id,
                'purchase_id' => $order->id,
                'payment_status' => 'pending',
                'amount' => (float) $order->amount,
                'currency' => $order->currency,
                'title' => $order->title,
                'fee_percent' => (float) $order->fee_percent,
                'platform_fee' => (float) $order->platform_fee,
                'seller_amount' => (float) $order->seller_amount,
            ],
        ], 201);
    }

    public function confirmPayment(Request $request, $orderId): JsonResponse
    {
        if (! Schema::hasTable('store_orders')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 503);
        }

        $order = StoreOrder::find($orderId);
        $buyerId = Auth::guard('api')->id() ?: Auth::id();

        if (! $order || (int) $order->buyer_id !== (int) $buyerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Already paid.',
                'data' => [
                    'order_id' => $order->id,
                    'payment_status' => 'paid',
                    'amount' => (float) $order->amount,
                    'platform_fee' => (float) $order->platform_fee,
                    'seller_amount' => (float) $order->seller_amount,
                ],
            ]);
        }

        $request->validate([
            'payment_id' => 'required|string|max:191',
            'payment_method' => 'required|in:paypal,stripe,crypto',
        ]);

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $order->amount,
            'store_order',
            $order->id
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        $order->payment_status = 'paid';
        $order->payment_method = $request->input('payment_method');
        $order->payment_id = $verified['payment_id'];
        $order->save();

        $this->recordMarketplaceSaleMoneyFlow(
            'store_order',
            (float) $order->amount,
            (float) $order->platform_fee,
            (float) $order->seller_amount,
            'store_order',
            $order->id,
            $verified['payment_id'],
            (int) $buyerId,
            $order->seller_id ? (int) $order->seller_id : null,
            'USD',
            'Store order payment'
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Seller will receive payout minus platform fee.',
            'data' => [
                'order_id' => $order->id,
                'payment_status' => 'paid',
                'amount' => (float) $order->amount,
                'fee_percent' => (float) $order->fee_percent,
                'platform_fee' => (float) $order->platform_fee,
                'seller_amount' => (float) $order->seller_amount,
            ],
        ]);
    }
}
