<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Facades;

use Illuminate\Support\Facades\Facade;
use Umbrellio\LaravelHeavyJobs\Stores\PayloadStoreManager;

/**
 * @method static string store(string $id, $job)
 * @method static mixed get(string $identifier)
 * @method static mixed getFailed(string $identifier)
 * @method static void remove(string $identifier)
 * @method static void removeFailed(string $identifier)
 * @method static void markAsFailed(string $identifier)
 * @method static void flushFailed()
 * @method static string generateId()
 *
 * @see PayloadStoreManager
 */
class HeavyJobsStore extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'heavy-jobs-store';
    }
}
