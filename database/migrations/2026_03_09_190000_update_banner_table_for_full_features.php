<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banner', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('banner', 'tagline')) {
                $table->string('tagline')->nullable()->after('title');
            }
            
            if (!Schema::hasColumn('banner', 'description')) {
                $table->text('description')->nullable()->after('tagline');
            }
            
            if (!Schema::hasColumn('banner', 'banner_type')) {
                $table->enum('banner_type', ['standard', 'gif', 'html5', 'video'])->default('standard')->after('description');
            }
            
            if (!Schema::hasColumn('banner', 'banner_size')) {
                $table->string('banner_size')->after('banner_type');
            }
            
            if (!Schema::hasColumn('banner', 'destination_url')) {
                $table->string('destination_url')->after('img');
            }
            
            if (!Schema::hasColumn('banner', 'cta_text')) {
                $table->string('cta_text')->nullable()->after('destination_url');
            }
            
            if (!Schema::hasColumn('banner', 'country')) {
                $table->string('country')->after('cta_text');
            }
            
            if (!Schema::hasColumn('banner', 'city')) {
                $table->string('city')->after('country');
            }
            
            if (!Schema::hasColumn('banner', 'views')) {
                $table->unsignedBigInteger('views')->default(0)->after('city');
            }
            
            if (!Schema::hasColumn('banner', 'clicks')) {
                $table->unsignedBigInteger('clicks')->default(0)->after('views');
            }
            
            if (!Schema::hasColumn('banner', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('clicks');
            }
            
            if (!Schema::hasColumn('banner', 'is_promoted')) {
                $table->boolean('is_promoted')->default(false)->after('status');
            }
            
            if (!Schema::hasColumn('banner', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_promoted');
            }
            
            if (!Schema::hasColumn('banner', 'is_sponsored')) {
                $table->boolean('is_sponsored')->default(false)->after('is_featured');
            }
            
            if (!Schema::hasColumn('banner', 'price')) {
                $table->decimal('price', 10, 2)->default(0.00)->after('is_sponsored');
            }
            
            if (!Schema::hasColumn('banner', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('price');
            }
            
            if (!Schema::hasColumn('banner', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable()->after('payment_status');
            }
            
            if (!Schema::hasColumn('banner', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_transaction_id');
            }
            
            if (!Schema::hasColumn('banner', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('banner', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('expires_at');
            }
            
            if (!Schema::hasColumn('banner', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('is_active');
            }
            
            if (!Schema::hasColumn('banner', 'service_id')) {
                $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('cascade')->after('user_id');
            }
            
            if (!Schema::hasColumn('banner', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')->nullable()->constrained('ad_pricing_plans')->onDelete('set null')->after('service_id');
            }
            
            if (!Schema::hasColumn('banner', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('banner_categories')->onDelete('set null')->after('pricing_plan_id');
            }
            
            // Add indexes
            $table->index(['status', 'is_active']);
            $table->index(['payment_status']);
            $table->index(['country']);
            $table->index(['views']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner', function (Blueprint $table) {
            // Drop columns that were added
            $columnsToDrop = [
                'tagline', 'description', 'banner_type', 'banner_size', 'destination_url', 
                'cta_text', 'country', 'city', 'views', 'clicks', 'status', 
                'is_promoted', 'is_featured', 'is_sponsored', 'price', 'payment_status',
                'payment_transaction_id', 'paid_at', 'expires_at', 'is_active',
                'user_id', 'service_id', 'pricing_plan_id', 'category_id'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('banner', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
