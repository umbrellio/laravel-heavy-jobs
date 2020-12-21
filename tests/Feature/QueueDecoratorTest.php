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

    /**
     * @test
     */
    public function queueWrapHeavyJob(): void
    {
        Queue::push(new FakeJob('foo'));
        Queue::assertPushed(HeavyJob::class);
    }

    /**
     * @test
     */
    public function queueWrapHeavyJobCondition(): void
    {
        Queue::push(new FakeConditionJob(false));
        Queue::assertPushed(FakeConditionJob::class);

        Queue::push(new FakeConditionJob(true));
        Queue::assertPushed(HeavyJob::class);
    }

    /**
     * @test
     */
    public function queueMarkHeavyJob(): void
    {
        Queue::push(new FakeJob('foo'));
        Queue::assertPushed(HeavyJob::class, function (HeavyJob $job) {
            $this->assertFalse($job->IsHandlePayloadRemove());

            return true;
        });
    }

    /**
     * @test
     */
    public function queueNotWrapCommonJob(): void
    {
        $job = Mockery::mock(Job::class);

        Queue::push($job);
        Queue::assertPushed(get_class($job));
    }
}
