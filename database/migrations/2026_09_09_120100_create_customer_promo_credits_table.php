<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_promo_credits')) {
            Schema::create('customer_promo_credits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('business_id')->nullable()->index();
                $table->unsignedBigInteger('promo_reward_code_id')->nullable()->index();
                $table->string('code_snapshot', 64)->nullable();
                $table->string('platform', 32)->default('wwa'); // wwa | carservices
                $table->string('tier', 32); // promoted | featured | sponsored
                $table->unsignedInteger('quantity_total')->default(1);
                $table->unsignedInteger('quantity_remaining')->default(1);
                $table->unsignedInteger('duration_days')->default(7);
                $table->string('source', 32)->default('onboarding');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'tier', 'quantity_remaining']);
            });
        }

        if (! Schema::hasTable('promo_credit_usages')) {
            Schema::create('promo_credit_usages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_promo_credit_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('listing_type', 64); // promoted_advert | featured_advert | sponsored_advert
                $table->string('listing_id', 64);
                $table->string('tier', 32);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_credit_usages');
        Schema::dropIfExists('customer_promo_credits');
    }
};
