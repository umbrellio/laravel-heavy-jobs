<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Mockery;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueDecorator;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeConditionJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;

class QueueDecoratorTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::swap(new QueueDecorator(new QueueFake($this->app)));
    }

    public function testQueueWrapHeavyJob(): void
    {
        Queue::push(new FakeJob('key', [1, 2, 3]));
        Queue::assertPushed(HeavyJob::class);
    }

    public function testQueueWrapHeavyJobCondition(): void
    {
        Queue::push(new FakeConditionJob(false));
        Queue::assertPushed(FakeConditionJob::class);

        Queue::push(new FakeConditionJob(true));
        Queue::assertPushed(HeavyJob::class);
    }

    public function testQueueMarkHeavyJob(): void
    {
        Queue::push(new FakeJob('key', [1, 2, 3]));
        Queue::assertPushed(HeavyJob::class, function (HeavyJob $job) {
            $this->assertTrue($job->isPushed());

            return true;
        });
    }

    public function testQueueNotWrapCommonJob(): void
    {
        $job = Mockery::mock(Job::class);

        Queue::push($job);
        Queue::assertPushed(get_class($job));
    }
}
