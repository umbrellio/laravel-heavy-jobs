<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests;

use Orchestra\Testbench\TestCase;
use Umbrellio\LaravelHeavyJobs\HeavyJobsServiceProvider;
use Umbrellio\LaravelHeavyJobs\Stores\PayloadStoreManager;

abstract class IntegrationTest extends TestCase
{
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
        $defaultConfig = include(__DIR__ . '/../config/heavy-jobs.php');
        foreach ($defaultConfig as $key => $value) {
            $app['config']->set("heavy-jobs.{$key}", $value);
        }

        $app['config']->set('queue.default', 'sync');
        $app['config']->set('queue.failed', null);
    }
}
