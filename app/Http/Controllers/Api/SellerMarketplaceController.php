<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerMarketplacePayout;
use App\Services\SellerMarketplaceEarningsService;
use App\Support\CryptoRails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SellerMarketplaceController extends Controller
{
    public function earnings(SellerMarketplaceEarningsService $service): JsonResponse
    {
        $userId = (int) Auth::id();

        return response()->json([
            'success' => true,
            'data' => $service->summary($userId),
        ]);
    }

    public function payouts(): JsonResponse
    {
        if (! Schema::hasTable('seller_marketplace_payouts')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $rows = SellerMarketplacePayout::query()
            ->forUser(Auth::id())
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function requestPayout(Request $request, SellerMarketplaceEarningsService $service): JsonResponse
    {
        if (! Schema::hasTable('seller_marketplace_payouts')) {
            return response()->json([
                'success' => false,
                'message' => 'Seller payouts not available — run migrations.',
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:25',
            'method' => 'nullable|string|max:50',
            'payout_details' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'crypto_network' => 'nullable|string|in:trc20,erc20,polygon',
            'crypto_address' => 'nullable|string|max:191',
            'crypto_currency' => 'nullable|string|max:16',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) Auth::id();
        $amount = round((float) $request->input('amount'), 2);
        $summary = $service->summary($userId);
        $available = (float) ($summary['totals']['available'] ?? 0);

        if ($amount > $available) {
            return response()->json([
                'success' => false,
                'message' => 'Amount exceeds available seller balance',
                'data' => ['available' => $available],
            ], 422);
        }

        $method = strtolower((string) $request->input('method', 'crypto'));
        $cryptoNetwork = strtolower((string) $request->input('crypto_network', ''));
        $cryptoAddress = trim((string) $request->input('crypto_address', ''));
        $cryptoCurrency = $request->input('crypto_currency');

        if ($method === 'crypto') {
            $customer = Auth::user();
            if ($cryptoAddress === '' && $customer && ! empty($customer->crypto_wallet_address)) {
                $cryptoAddress = (string) $customer->crypto_wallet_address;
                $cryptoNetwork = $cryptoNetwork ?: (string) ($customer->crypto_network ?: 'trc20');
            }
            if ($cryptoNetwork === '') {
                $cryptoNetwork = 'trc20';
            }
            $check = CryptoRails::validateAddress($cryptoAddress, $cryptoNetwork);
            if (! $check['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $check['message'],
                ], 422);
            }
            $cryptoAddress = $check['address'];
            $meta = CryptoRails::network($cryptoNetwork);
            $cryptoCurrency = $cryptoCurrency ?: ($meta['currency'] ?? 'USDT');
        }

        $payout = SellerMarketplacePayout::create([
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => 'USD',
            'method' => $method,
            'payout_details' => $request->input('payout_details')
                ?: ($method === 'crypto' ? ($cryptoNetwork.' · '.$cryptoAddress) : null),
            'notes' => $request->input('notes'),
            'status' => 'pending',
            'reference' => 'SELL-'.strtoupper(Str::random(8)),
            'crypto_network' => $method === 'crypto' ? $cryptoNetwork : null,
            'crypto_address' => $method === 'crypto' ? $cryptoAddress : null,
            'crypto_currency' => $method === 'crypto' ? $cryptoCurrency : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Seller payout request submitted. Admin will send after approval.',
            'data' => $payout,
        ], 201);
    }
}
