<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\functional\Decorators;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LaravelHeavyJobs\Decorators\QueueDecorator;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures\FakeConditionJob;
use Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\FunctionalTestCase;

class QueueDecoratorTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::swap(new QueueDecorator(new QueueFake($this->app)));
    }

    #[Test]
    public function queueWrapHeavyJob(): void
    {
        Queue::push(new FakeJob('foo'));
        Queue::assertPushed(HeavyJob::class);
    }

    #[Test]
    public function queueWrapHeavyJobCondition(): void
    {
        Queue::push(new FakeConditionJob(false));
        Queue::assertPushed(FakeConditionJob::class);

        Queue::push(new FakeConditionJob(true));
        Queue::assertPushed(HeavyJob::class);
    }

    #[Test]
    public function queueMarkHeavyJob(): void
    {
        Queue::push(new FakeJob('foo'));
        Queue::assertPushed(HeavyJob::class, function (HeavyJob $job) {
            $this->assertFalse($job->IsHandlePayloadRemove());

            return true;
        });
    }

    #[Test]
    public function queueNotWrapCommonJob(): void
    {
        $job = Mockery::mock(Job::class);

        Queue::push($job);
        Queue::assertPushed(get_class($job));
    }
}
