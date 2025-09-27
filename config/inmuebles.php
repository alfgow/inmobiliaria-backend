<?php

return [
    'images' => [
        // Tiempo de vida de las URLs temporales generadas para las imágenes.
        'url_ttl_minutes' => (int) env('INMUEBLES_IMAGE_URL_TTL_MINUTES', 60),
        'quality' => (int) env('INMUEBLES_IMAGE_QUALITY', 85),
        'normalized' => [
            'width' => (int) env('INMUEBLES_IMAGE_WIDTH', 1200),
            'height' => (int) env('INMUEBLES_IMAGE_HEIGHT', 800),
        ],
        'thumbnail' => [
            'width' => (int) env('INMUEBLES_THUMB_WIDTH', 300),
            'height' => (int) env('INMUEBLES_THUMB_HEIGHT', 200),
        ],
        'watermark' => [
            'path' => env('INMUEBLES_WATERMARK_PATH', resource_path('images/MarcaDeAgua_GDE.png')),
            'disk' => env('INMUEBLES_WATERMARK_DISK'),
            'position' => env('INMUEBLES_WATERMARK_POSITION', 'bottom-right'),
            'offset_x' => (int) env('INMUEBLES_WATERMARK_OFFSET_X', 24),
            'offset_y' => (int) env('INMUEBLES_WATERMARK_OFFSET_Y', 24),
            'preview_disk' => env('INMUEBLES_WATERMARK_PREVIEW_DISK', env('INMUEBLES_WATERMARK_DISK')),
            'preview_path' => env('INMUEBLES_WATERMARK_PREVIEW_PATH', ''),
            'preview_ttl' => (int) env('INMUEBLES_WATERMARK_PREVIEW_TTL', 10),
        ],
    ],
];
