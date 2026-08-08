<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Paid-only: bump any $0 listing / promotion plans
        $tables = [
            'pricing_plans',
            'buy_sell_promotion_plans',
            'vehicle_pricing_plans',
            'job_pricing_plans',
            'packages',
            'ad_pricing_plans',
            'promotion_plans',
            'sponsored_pricing_plans',
            'affiliate_upsell_plans',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! Schema::hasColumn($table, 'price')) {
                continue;
            }
            DB::table($table)->where('price', '<=', 0)->update([
                'price' => 29,
                'updated_at' => now(),
            ]);
            // Rename free-looking plan names
            if (Schema::hasColumn($table, 'name')) {
                DB::table($table)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%Free%')
                            ->orWhere('name', 'like', '%Basic Listing%')
                            ->orWhere('name', 'Basic')
                            ->orWhere('name', 'Standard');
                    })
                    ->where('price', '<', 29)
                    ->update(['price' => 29, 'updated_at' => now()]);
            }
        }

        if (! Schema::hasTable('seller_contact_messages')) {
            Schema::create('seller_contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('hub')->index(); // buysell, vehicles, property, featured, etc.
                $table->unsignedBigInteger('listing_id')->index();
                $table->unsignedBigInteger('seller_user_id')->nullable()->index();
                $table->unsignedBigInteger('buyer_user_id')->nullable()->index();
                $table->string('buyer_name');
                $table->string('buyer_email');
                $table->string('buyer_phone')->nullable();
                $table->string('contact_method')->default('email');
                $table->text('message');
                $table->string('status')->default('new'); // new, read, replied
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_contact_messages');
    }
};
