<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Queue\Events\JobFailed;
use RuntimeException;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJobProvider;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;

class FailedJobProviderDecoratorTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('queue.failer', FakeFailedJobProvider::class);
    }

    /**
     * @test
     */
    public function forget(): void
    {
        $this->app['events']->listen(
            JobFailed::class,
            function (JobFailed $event) use (&$heavyPayloadId, &$logId): void {
                $logId = $this->logFailedJob($event);
                $heavyPayloadId = $event->job->payload()['heavy-payload-id'];
            }
        );

        $this->dispatchJobIgnoreException();

        $this->assertNotEmpty(HeavyJobsStore::getFailed($heavyPayloadId));

        $this->artisan('queue:forget', [
            'id' => $logId,
        ])->assertExitCode(0);

        $this->assertEmpty(HeavyJobsStore::getFailed($heavyPayloadId));
    }

    /**
     * @test
     */
    public function flush(): void
    {
        $ids = [];
        $this->app['events']->listen(
            JobFailed::class,
            function (JobFailed $event) use (&$ids): void {
                $this->logFailedJob($event);
                $ids[] = $event->job->payload()['heavy-payload-id'];
            }
        );

        $this->dispatchJobIgnoreException();
        $this->dispatchJobIgnoreException();
        $this->dispatchJobIgnoreException();

        foreach ($ids as $id) {
            $this->assertNotEmpty(HeavyJobsStore::getFailed($id));
        }

        $this->artisan('queue:flush')
            ->assertExitCode(0);

        foreach ($ids as $id) {
            $this->assertEmpty(HeavyJobsStore::getFailed($id));
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

    private function dispatchJobIgnoreException(): void
    {
        try {
            FakeFailedJob::dispatch();
        } catch (RuntimeException $runtimeException) {
            if ($runtimeException->getMessage() !== 'Some exception.') {
                throw $runtimeException;
            }
        }
    }
}
