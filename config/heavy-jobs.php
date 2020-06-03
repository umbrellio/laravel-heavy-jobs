<?php

return [
    'driver' => env('HEAVY_JOBS_DRIVER', 'redis'),
    'parameters' => [
        'connection' => env('HEAVY_JOBS_CONNECTION', 'default'),
    ],
    /**
     * Время жизни (в минутах) payload'а для job'ок завершившихся ошибкой.
     * Если параметр равен -1 - payload хранится до тех пор, пока не будет удален вручную.
     *
     * Важно, если используется horizon или ему подобные очереди, failed_job_lifetime должно быть равно trim.failed,
     * или быть чуток больше.
     *
     * Важно, таймаут высчитывается при каждом обращении к payload. По этому данные могут быть удалены, чуть позже чем
     * наступит их реальный таймаут.
     */
    'failed_job_lifetime' => -1,
];
