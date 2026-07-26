<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | The driver Laravel's image component uses to decode and re-encode
    | uploads. Both "gd" and "imagick" are available in this environment;
    | gd is lighter and enough for the JPEG/PNG/WebP catalog images.
    |
    */

    'default' => env('IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Product Image Processing
    |--------------------------------------------------------------------------
    |
    | Every admin upload is scaled down to fit these bounds (aspect ratio is
    | preserved and smaller images are never upscaled) and re-encoded to the
    | format below. A 10 MB camera JPEG typically lands under 200 KB.
    |
    */

    'products' => [
        'max_width' => 1600,
        'max_height' => 1600,
        'format' => 'webp',
        'quality' => 75,
    ],

];
