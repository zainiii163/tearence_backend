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
        Schema::create('staff_management', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('staff_id');
            $table->unsignedInteger('customer_id'); // Owner (business/store owner)
            $table->unsignedInteger('staff_customer_id'); // Staff member customer ID
            $table->enum('entity_type', ['business', 'store']); // Type of entity staff can manage
            $table->unsignedInteger('entity_id'); // Business ID or Store ID
            $table->json('permissions')->nullable(); // Specific permissions for this staff member
            $table->enum('role', ['admin', 'editor', 'viewer'])->default('viewer'); // Staff role
            $table->boolean('can_post_ads')->default(false);
            $table->boolean('can_edit_ads')->default(false);
            $table->boolean('can_delete_ads')->default(false);
            $table->boolean('can_manage_payments')->default(false);
            $table->boolean('can_view_analytics')->default(false);
            $table->boolean('can_manage_staff')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('cascade');
            
            $table->foreign('staff_customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('cascade');

            // Indexes
            $table->index('customer_id');
            $table->index('staff_customer_id');
            $table->index(['entity_type', 'entity_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_management');
    }
};
