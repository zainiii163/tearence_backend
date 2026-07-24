<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform marketplace take-rate
    |--------------------------------------------------------------------------
    | Clive: 15% of every sale (books, images, templates, online software).
    */
    'platform_fee_percent' => (float) env('PLATFORM_FEE_PERCENT', 15),
];
