<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\functional\Decorators;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue as QueueContract;
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

    #[Test]
    public function delegatesPendingSize(): void
    {
        $innerQueue = Mockery::mock(QueueContract::class);
        $innerQueue->shouldReceive('pendingSize')->with('default')->once()->andReturn(3);

        $this->assertSame(3, (new QueueDecorator($innerQueue))->pendingSize('default'));
    }

    #[Test]
    public function delegatesDelayedSize(): void
    {
        $innerQueue = Mockery::mock(QueueContract::class);
        $innerQueue->shouldReceive('delayedSize')->with('default')->once()->andReturn(2);

        $this->assertSame(2, (new QueueDecorator($innerQueue))->delayedSize('default'));
    }

    #[Test]
    public function delegatesReservedSize(): void
    {
        $innerQueue = Mockery::mock(QueueContract::class);
        $innerQueue->shouldReceive('reservedSize')->with('default')->once()->andReturn(1);

        $this->assertSame(1, (new QueueDecorator($innerQueue))->reservedSize('default'));
    }

    #[Test]
    public function delegatesCreationTimeOfOldestPendingJob(): void
    {
        $innerQueue = Mockery::mock(QueueContract::class);
        $innerQueue->shouldReceive('creationTimeOfOldestPendingJob')->with('default')->once()->andReturn(1716220800);

        $this->assertSame(1716220800, (new QueueDecorator($innerQueue))->creationTimeOfOldestPendingJob('default'));
    }
}
