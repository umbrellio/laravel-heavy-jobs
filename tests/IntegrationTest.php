<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;
use Umbrellio\LaravelHeavyJobs\HeavyJobsServiceProvider;
use Umbrellio\LaravelHeavyJobs\Stores\PayloadStoreManager;
use Umbrellio\LaravelHeavyJobs\Stores\RedisStore;

abstract class IntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        // автоматически очищаем данные, после каждого теста.
        Redis::del(RedisStore::JOBS_HASH_KEY);
        Redis::del(RedisStore::FAILED_JOBS_HASH_KEY);

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [HeavyJobsServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['heavy-jobs-store' => PayloadStoreManager::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('queue.failed', null);
    }
}
