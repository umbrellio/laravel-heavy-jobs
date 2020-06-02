<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Queue\Events\JobFailed;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJobProvider;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;
use \Throwable;

class FailedJobProviderDecoratorTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('queue.failer', FakeFailedJobProvider::class);
    }

    public function testForget(): void
    {
        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) use (&$heavyPayloadId, &$logId) {
            $logId = $this->logFailedJob($event);
            $heavyPayloadId = $event->job->payload()['heavy-payload-id'];
        });

        $this->dispatchJobIgnoreException(['foo' => 'bar']);

        $this->assertNotEmpty(HeavyJobsStore::get($heavyPayloadId));

        $this->artisan('queue:forget', ['id' => $logId])->assertExitCode(0);

        $this->assertEmpty(HeavyJobsStore::get($heavyPayloadId));
    }

    public function testFlush(): void
    {
        $ids = [];
        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) use (&$ids) {
            $this->logFailedJob($event);

            $ids[] = $event->job->payload()['heavy-payload-id'];
        });

        $this->dispatchJobIgnoreException(['foo' => 1]);
        $this->dispatchJobIgnoreException(['bar' => 2]);
        $this->dispatchJobIgnoreException(['baz' => 3]);

        foreach ($ids as $id) {
            $this->assertNotEmpty(HeavyJobsStore::get($id));
        }

        $this->artisan('queue:flush')->assertExitCode(0);

        foreach ($ids as $id) {
            $this->assertEmpty(HeavyJobsStore::get($id));
        }
    }

    private function logFailedJob(JobFailed $event): int
    {
        // т.к. в драйвере очереди sync нет сохранения невыполненных job'ок, мы его имитируем.
        return resolve('queue.failer')->log(
            $event->connectionName, $event->job->getQueue(),
            $event->job->getRawBody(), $event->exception
        );
    }

    private function dispatchJobIgnoreException(array $data): void
    {
        try {
            FakeFailedJob::dispatch($data);
        } catch (Throwable $e) {
            // ignore exception;
        }
    }
}
