<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests;

use Illuminate\Support\Facades\Redis;
use Orchestra\Testbench\TestCase;
use Umbrellio\LaravelHeavyJobs\HeavyJobsServiceProvider;
use Umbrellio\LaravelHeavyJobs\Stores\PayloadStoreManager;
use Umbrellio\LaravelHeavyJobs\Stores\RedisStore;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\Work\WorkRepository;

abstract class IntegrationTest extends TestCase
{
    /**
     * @var WorkRepository
     */
    protected $workRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workRepository = new WorkRepository();
        $this->app->singleton(WorkRepository::class, function () {
            return $this->workRepository;
        });
    }

    protected function tearDown(): void
    {
        // автоматически очищаем данные, после каждого теста.
        Redis::del(RedisStore::JOB_PAYLOADS_KEY);
        Redis::del(RedisStore::FAILED_JOB_PAYLOADS_KEY);
        Redis::del(RedisStore::LIFETIME_FAILED_JOB_PAYLOADS_KEY);

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
