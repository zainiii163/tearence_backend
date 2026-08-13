<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 crypto rails: customer wallets, payment invoices, affiliate payout crypto fields.
 * WWA does not custody user crypto — NOWPayments moves funds on-chain.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer')) {
            Schema::table('customer', function (Blueprint $table) {
                if (! Schema::hasColumn('customer', 'crypto_wallet_address')) {
                    $table->string('crypto_wallet_address', 191)->nullable();
                }
                if (! Schema::hasColumn('customer', 'crypto_network')) {
                    $table->string('crypto_network', 32)->nullable();
                }
                if (! Schema::hasColumn('customer', 'crypto_wallet_verified_at')) {
                    $table->timestamp('crypto_wallet_verified_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'crypto_wallet_address')) {
                    $table->string('crypto_wallet_address', 191)->nullable();
                }
                if (! Schema::hasColumn('users', 'crypto_network')) {
                    $table->string('crypto_network', 32)->nullable();
                }
                if (! Schema::hasColumn('users', 'crypto_wallet_verified_at')) {
                    $table->timestamp('crypto_wallet_verified_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('affiliate_payouts')) {
            Schema::table('affiliate_payouts', function (Blueprint $table) {
                if (! Schema::hasColumn('affiliate_payouts', 'crypto_network')) {
                    $table->string('crypto_network', 32)->nullable();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'crypto_address')) {
                    $table->string('crypto_address', 191)->nullable();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'crypto_currency')) {
                    $table->string('crypto_currency', 16)->nullable();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'provider_payout_id')) {
                    $table->string('provider_payout_id', 191)->nullable()->index();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'tx_hash')) {
                    $table->string('tx_hash', 191)->nullable();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'provider')) {
                    $table->string('provider', 32)->nullable();
                }
                if (! Schema::hasColumn('affiliate_payouts', 'raw_webhook_json')) {
                    $table->json('raw_webhook_json')->nullable();
                }
            });
        }

        if (! Schema::hasTable('crypto_payments')) {
            Schema::create('crypto_payments', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 32)->default('nowpayments')->index();
                $table->string('ledger_id', 191)->unique();
                $table->string('provider_invoice_id', 191)->nullable()->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('currency', 8)->default('USD');
                $table->string('pay_currency', 32)->nullable();
                $table->string('network', 32)->nullable();
                $table->decimal('amount', 12, 2);
                $table->decimal('pay_amount', 24, 8)->nullable();
                $table->string('pay_address', 191)->nullable();
                $table->string('status', 32)->default('waiting')->index();
                $table->string('tx_hash', 191)->nullable();
                $table->string('order_id', 64)->nullable()->index();
                $table->string('upsell_type', 64)->nullable();
                $table->string('upsell_id', 64)->nullable();
                $table->string('invoice_url', 500)->nullable();
                $table->boolean('mock')->default(false);
                $table->json('raw_provider_json')->nullable();
                $table->json('raw_webhook_json')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_payments');

        if (Schema::hasTable('affiliate_payouts')) {
            Schema::table('affiliate_payouts', function (Blueprint $table) {
                foreach ([
                    'crypto_network',
                    'crypto_address',
                    'crypto_currency',
                    'provider_payout_id',
                    'tx_hash',
                    'provider',
                    'raw_webhook_json',
                ] as $column) {
                    if (Schema::hasColumn('affiliate_payouts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        foreach (['customer', 'users'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['crypto_wallet_address', 'crypto_network', 'crypto_wallet_verified_at'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
