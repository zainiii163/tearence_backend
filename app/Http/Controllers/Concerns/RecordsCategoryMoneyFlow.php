<?php

namespace App\Http\Controllers\Concerns;

use App\Services\CategoryMoneyFlowService;
use App\Support\MarketplaceCategoryMap;
use Throwable;

/**
 * Record Clive category money-flow rows after a verified payment.
 */
trait RecordsCategoryMoneyFlow
{
    protected function moneyFlow(): CategoryMoneyFlowService
    {
        return app(CategoryMoneyFlowService::class);
    }

    protected function recordMarketplaceSaleMoneyFlow(
        string $purchaseType,
        float $gross,
        float $platformFee,
        float $sellerAmount,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        ?int $payeeUserId = null,
        string $currency = 'USD',
        string $description = ''
    ): void {
        try {
            $this->moneyFlow()->recordSaleSplit(
                MarketplaceCategoryMap::fromPurchaseType($purchaseType),
                $gross,
                $platformFee,
                $sellerAmount,
                $sourceType,
                $sourceId,
                $paymentId,
                $payerUserId,
                $payeeUserId,
                $currency,
                $description
            );
        } catch (Throwable $e) {
            // Never block checkout on ledger write
            report($e);
        }
    }

    protected function recordPlatformFeeMoneyFlow(
        string $purchaseType,
        float $amount,
        string $subtype,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        string $currency = 'USD',
        string $description = ''
    ): void {
        try {
            $this->moneyFlow()->recordPlatformIncome(
                MarketplaceCategoryMap::fromPurchaseType($purchaseType),
                $amount,
                $subtype,
                $sourceType,
                $sourceId,
                $paymentId,
                $payerUserId,
                $currency,
                $description
            );
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function recordOtherMoneyFlow(
        string $purchaseType,
        float $amount,
        string $subtype,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        ?int $payeeUserId = null,
        string $currency = 'USD',
        string $description = ''
    ): void {
        try {
            $this->moneyFlow()->recordOther(
                MarketplaceCategoryMap::fromPurchaseType($purchaseType),
                $amount,
                $subtype,
                $sourceType,
                $sourceId,
                $paymentId,
                $payerUserId,
                $payeeUserId,
                $currency,
                $description
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
