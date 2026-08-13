<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform marketplace take-rate
    |--------------------------------------------------------------------------
    | Listing / advert fees (paid, promoted, featured, sponsored) → 100% WWA.
    | Product sales (books, buy-sell, images, services, templates) → buyer pays
    | WWA checkout; PLATFORM_FEE_PERCENT stays with WWA; remainder credited to
    | the seller and withdrawable via seller marketplace crypto payouts.
    | Platform-owned products (business tools) → 100% WWA.
    */
    'platform_fee_percent' => (float) env('PLATFORM_FEE_PERCENT', 15),
];