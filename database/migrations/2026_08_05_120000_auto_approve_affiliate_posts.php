<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * For now: auto-approve existing pending affiliate content so posts go live immediately.
     * New creates already set status=approved in AffiliateController.
     */
    public function up(): void
    {
        DB::table('user_affiliate_posts')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'is_active' => true,
                'updated_at' => now(),
            ]);

        DB::table('business_affiliate_offers')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Intentionally left empty — do not re-pend approved live content.
    }
};
