<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use RuntimeException;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueDecorator;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueManagerDecorator;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;

class HeavyJobsDispatchTest extends IntegrationTest
{
    /**
     * @test
     */
    public function decoratorIntegration(): void
    {
        /** @var QueueManagerDecorator $queueManager */
        $queueManager = $this->app->get('queue');
        $this->assertInstanceOf(QueueManagerDecorator::class, $queueManager);
        $this->assertInstanceOf(QueueDecorator::class, $queueManager->connection());
    }

    /**
     * @test
     */
    public function storeQueuePayload(): void
    {
        $workName = 'foo';

        $this->app['events']->listen(
            JobProcessed::class,
            function (JobProcessed $event) use ($workName): void {
                $this->assertTrue($this->workRepository->getWork($workName)->isCompleted());

                $jobPayload = $event->job->payload();

                $this->assertSame(HeavyJob::class, $jobPayload['data']['commandName']);
                $this->assertEmpty(HeavyJobsStore::get($jobPayload['heavy-payload-id']));
            }
        );

        FakeJob::dispatch($workName);
    }

    /**
     * @test
     */
    public function markAsFailed(): void
    {
        HeavyJobsStore::spy()
            ->shouldReceive('get')->andReturn(new FakeFailedJob())
            ->shouldReceive('markAsFailed')->once();

        $this->expectExceptionObject(new RuntimeException('Some exception.'));
        FakeFailedJob::dispatch();
    }

    /**
     * @test
     */
    public function lifetime(): void
    {
        config(['heavy-jobs.failed_job_lifetime' => 1]);

        $this->app['events']->listen(
            JobFailed::class,
            function (JobFailed $event) use (&$heavyPayloadId): void {
                $heavyPayloadId = $event->job->payload()['heavy-payload-id'];
            }
        );

        $this->dispatchJobIgnoreException();

        $this->assertNotEmpty(HeavyJobsStore::getFailed($heavyPayloadId));

        sleep(1);
        // при вызове get, произойдет очистка таймаута.
        HeavyJobsStore::get('non-exists-id');

        $this->assertEmpty(HeavyJobsStore::getFailed($heavyPayloadId));

        config(['heavy-jobs.failed_job_lifetime' => -1]);
    }

    /**
     * @test
     */
    public function persistsLifetime(): void
    {
        config(['heavy-jobs.failed_job_lifetime' => -1]);

        $this->app['events']->listen(
            JobFailed::class,
            function (JobFailed $event) use (&$heavyPayloadId): void {
                $heavyPayloadId = $event->job->payload()['heavy-payload-id'];
            }
        );

        $this->dispatchJobIgnoreException();

        $this->assertNotEmpty(HeavyJobsStore::getFailed($heavyPayloadId));

        sleep(1);
        // при вызове get, произойдет очистка таймаута.
        HeavyJobsStore::get('non-exists-id');

        $this->assertNotEmpty(HeavyJobsStore::getFailed($heavyPayloadId));
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
