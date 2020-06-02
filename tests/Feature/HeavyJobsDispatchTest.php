<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Cache;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueDecorator;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueManagerDecorator;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeFailedJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;
use RuntimeException;

class HeavyJobsDispatchTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDecoratorIntegration(): void
    {
        /** @var QueueManagerDecorator $queueManager */
        $queueManager = $this->app->get('queue');
        $this->assertInstanceOf(QueueManagerDecorator::class, $queueManager);
        $this->assertInstanceOf(QueueDecorator::class, $queueManager->connection());
    }

    public function testStoreQueuePayload(): void
    {
        $key = 'foo-bar';
        $data = ['foo' => 'bar'];

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) use ($key, $data): void {
            $this->assertSame($data, Cache::get($key));

            $jobPayload = $event->job->payload();

            $this->assertSame(HeavyJob::class, $jobPayload['data']['commandName']);
            $this->assertEmpty(HeavyJobsStore::get($jobPayload['heavy-payload-id']));
        });

        FakeJob::dispatch($key, $data);
    }

    public function testMarkAsFailed(): void
    {
        $data = ['foo' => 'bar'];

        HeavyJobsStore::spy()
            ->shouldReceive('get')->andReturn(new FakeFailedJob($data))
            ->shouldReceive('markAsFailed')->once();

        $this->expectExceptionObject(new RuntimeException('Some exception.'));
        FakeFailedJob::dispatch($data);
    }
}
