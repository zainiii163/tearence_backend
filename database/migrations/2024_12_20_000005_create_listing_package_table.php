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
        Schema::create('listing_package', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('package_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('listing_days')->nullable();
            $table->integer('promo_days')->nullable();
            $table->boolean('promo_show_promoted_area')->default(false);
            $table->boolean('promo_show_featured_area')->default(false);
            $table->boolean('promo_show_at_top')->default(false);
            $table->boolean('promo_sign')->default(false);
            $table->boolean('recommended_sign')->default(false);
            $table->boolean('auto_renewal')->default(false);
            $table->integer('pictures')->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('max_listings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_package');
    }
};

