<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | All monetary values are stored as integers in the smallest practical
    | unit. The Indonesian Rupiah (IDR) does not use fractional units in
    | day-to-day transactions, so amounts are stored as whole rupiah.
    |
    */

    'currency' => [
        'code' => env('KOST_CURRENCY', 'IDR'),
        'symbol' => env('KOST_CURRENCY_SYMBOL', 'Rp'),
        'locale' => env('KOST_CURRENCY_LOCALE', 'id_ID'),
        'decimals' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Public files (room photos, property images, logo) are served from the
    | "public" disk. Sensitive files (KTP, contracts, payment proofs) are
    | stored on the "private" disk and only exposed via authorized endpoints.
    |
    */

    'disk' => [
        'public' => 'public',
        'private' => 'private',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Defaults
    |--------------------------------------------------------------------------
    |
    | These values act as fallback defaults. Runtime values are resolved from
    | the Settings service (database) whenever available.
    |
    */

    'invoice' => [
        'prefix' => env('KOST_INVOICE_PREFIX', 'INV'),
        'due_days' => 10,
        'default_late_fee_type' => 'none',
        'default_late_fee_value' => 0,
        'prorate_enabled' => false,
        'prorate_basis_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Upload Rules
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_size_kb' => 4096,
        'image_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'document_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    ],

];
