<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | The disk root is UPLOADS_CONTAINER_PATH (see the "uploads" disk in
    | filesystems.php). Files are checked by content, never by client name.
    | "mimes" is the allowlist: detected mime type => stored extension.
    |
    */

    'max_mb' => (int) env('UPLOADS_MAX_MB', 5),

    'mimes' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],

    // WebP variant widths (px). Never upscaled; a full-size variant is always added.
    'variant_widths' => [480, 960, 1600],

    'webp_quality' => 82,

    // Skip variant generation above this many pixels (GD holds the bitmap in memory).
    'max_pixels' => 36_000_000,

];
