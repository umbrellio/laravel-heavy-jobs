<?php

return [
    'driver' => env('HEAVY_JOBS_DRIVER', 'redis'),
    'parameters' => [
        'connection' => env('HEAVY_JOBS_CONNECTION', 'default'),
    ],
];
