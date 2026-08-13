<?php

namespace App\Http\Controllers\Concerns;

use App\Services\PaymentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/**
 * Shared payment defence for confirm/complete endpoints.
 */
trait VerifiesClientPayments
{
    protected function verifyClientPaymentOrFail(
        Request $request,
        float $expectedAmount,
        string $purchaseType,
        string|int $purchaseId,
        string $expectedCurrency = 'USD',
        string $paymentIdKey = 'payment_id'
    ): array|JsonResponse {
        $paymentId = (string) (
            $request->input($paymentIdKey)
            ?: $request->input('payment_id')
            ?: $request->input('payment_reference')
            ?: $request->input('payment_transaction_id')
            ?: $request->input('transaction_id')
            ?: ''
        );

        try {
            return app(PaymentVerificationService::class)->assertVerifiedPayment(
                $paymentId,
                $expectedAmount,
                $expectedCurrency,
                $purchaseType,
                $purchaseId,
                (int) (auth('api')->id() ?: auth()->id() ?: 0) ?: null
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'defence' => 'payment_verification',
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.',
                'defence' => 'payment_verification',
            ], 422);
        }
    }
}
