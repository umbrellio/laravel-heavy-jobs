<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs;

use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Foundation\Application;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Umbrellio\LaravelHeavyJobs\Console\DatabaseStoreCommand;
use Umbrellio\LaravelHeavyJobs\Decorators\FailedJobProviderDecorator;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueManagerDecorator;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Stores\PayloadStoreManager;
use Umbrellio\LaravelHeavyJobs\Stores\StoreResolver;

class HeavyJobsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/heavy-jobs.php' => config_path('heavy-jobs.php'),
        ], ['heavy-jobs-config']);
    }

    public function register(): void
    {
        $this->app->singleton(StoreResolver::class);

        $this->app->singleton('heavy-jobs-store', function (Application $app) {
            return $app->make(PayloadStoreManager::class);
        });

        $this->app->extend(QueueFactory::class, function (QueueManager $manager, Application $app) {
            return new QueueManagerDecorator($manager, $app);
        });

        $this->app->extend(FailedJobProviderInterface::class, function (FailedJobProviderInterface $provider) {
            return new FailedJobProviderDecorator($provider);
        });

        $this->registerPayloadCleaner();
        $this->registerCommands();
    }

    private function registerPayloadCleaner(): void
    {
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $job = Arr::get($payload, 'data.command');
            if ($job instanceof HeavyJob) {
                return ['heavy-payload-id' => $job->getHeavyPayloadId()];
            }

            return [];
        });

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event): void {
            if ($heavyPayloadId = Arr::get($event->job->payload(), 'heavy-payload-id')) {
                HeavyJobsStore::remove($heavyPayloadId);
            }
        });
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([DatabaseStoreCommand::class]);
        }
    }
}
