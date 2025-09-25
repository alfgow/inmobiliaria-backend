<?php

return [
    'images' => [
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
            'position' => env('INMUEBLES_WATERMARK_POSITION', 'bottom-right'),
            'offset_x' => (int) env('INMUEBLES_WATERMARK_OFFSET_X', 24),
            'offset_y' => (int) env('INMUEBLES_WATERMARK_OFFSET_Y', 24),
        ],
    ],
];
