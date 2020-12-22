<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\functional\Jobs;

use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\FunctionalTestCase;

class HeavyJobTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function jobSerialize(): void
    {
        $fakeJob = new FakeJob('foo');
        $job = new HeavyJob($fakeJob);

        $serialized = serialize(clone $job);
        $this->assertStringNotContainsString('FakeJob', $serialized);

        /** @var HeavyJob $processingJob */
        $processingJob = unserialize($serialized);
        $this->assertSame($processingJob->getJob()->getWorkName(), $fakeJob->getWorkName());
        $this->assertFalse($processingJob->IsHandlePayloadRemove());
        $this->assertSame($processingJob->getHeavyPayloadId(), $job->getHeavyPayloadId());

        $storedJob = HeavyJobsStore::get($job->getHeavyPayloadId());
        $this->assertInstanceOf(FakeJob::class, $storedJob);
    }

    /**
     * @test
     */
    public function jobDestructorClearData(): void
    {
        $callback = function ($fakeJob) {
            $job = new HeavyJob($fakeJob);
            $job->handlePayloadRemove();

            serialize($job);

            $this->assertNotEmpty(HeavyJobsStore::get($job->getHeavyPayloadId()));

            return $job->getHeavyPayloadId();
        };
        $payloadId = $callback(new FakeJob('foo'));

        $this->assertEmpty(HeavyJobsStore::get($payloadId));
    }
}
