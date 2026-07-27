<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_templates')) {
            Schema::table('business_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('business_templates', 'is_premium')) {
                    $table->boolean('is_premium')->default(false)->after('is_catalog');
                }
                if (!Schema::hasColumn('business_templates', 'premium_until')) {
                    $table->timestamp('premium_until')->nullable()->after('is_premium');
                }
                if (!Schema::hasColumn('business_templates', 'premium_fee_paid')) {
                    $table->decimal('premium_fee_paid', 10, 2)->nullable()->after('premium_until');
                }
            });

            Schema::table('business_templates', function (Blueprint $table) {
                $table->index(['is_premium', 'premium_until']);
            });
        }

        if (!Schema::hasTable('template_settings')) {
            Schema::create('template_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('label', 255)->nullable();
                $table->timestamps();
            });

            DB::table('template_settings')->insert([
                [
                    'key' => 'premium_monthly_fee',
                    'value' => '5.00',
                    'label' => 'Premium listing fee (USD / month)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'premium_duration_days',
                    'value' => '30',
                    'label' => 'Premium listing duration (days)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_settings');

        if (Schema::hasTable('business_templates')) {
            Schema::table('business_templates', function (Blueprint $table) {
                if (Schema::hasColumn('business_templates', 'premium_fee_paid')) {
                    $table->dropColumn('premium_fee_paid');
                }
                if (Schema::hasColumn('business_templates', 'premium_until')) {
                    $table->dropColumn('premium_until');
                }
                if (Schema::hasColumn('business_templates', 'is_premium')) {
                    $table->dropColumn('is_premium');
                }
            });
        }
    }
};
