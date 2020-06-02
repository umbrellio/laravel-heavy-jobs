<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

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
    public function testDecoratorIntegration(): void
    {
        /** @var QueueManagerDecorator $queueManager */
        $queueManager = $this->app->get('queue');
        $this->assertInstanceOf(QueueManagerDecorator::class, $queueManager);
        $this->assertInstanceOf(QueueDecorator::class, $queueManager->connection());
    }

    public function testStoreQueuePayload(): void
    {
        $workName = 'foo';

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) use ($workName): void {
            $this->assertTrue($this->workRepository->getWork($workName)->isCompleted());

            $jobPayload = $event->job->payload();

            $this->assertSame(HeavyJob::class, $jobPayload['data']['commandName']);
            $this->assertEmpty(HeavyJobsStore::get($jobPayload['heavy-payload-id']));
        });

        FakeJob::dispatch($workName);
    }

    public function testMarkAsFailed(): void
    {
        HeavyJobsStore::spy()
            ->shouldReceive('get')->andReturn(new FakeFailedJob())
            ->shouldReceive('markAsFailed')->once();

        $this->expectExceptionObject(new RuntimeException('Some exception.'));
        FakeFailedJob::dispatch();
    }
}
