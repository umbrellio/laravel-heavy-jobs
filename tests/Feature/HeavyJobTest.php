<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\FakeJob;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;

class HeavyJobTest extends IntegrationTest
{
    public function testJobSerialize(): void
    {
        $fakeJob = new FakeJob('key', [1, 2, 3]);
        $job = new HeavyJob($fakeJob);

        $serialized = serialize(clone $job);
        $this->assertStringNotContainsString('FakeJob', $serialized);

        /** @var HeavyJob $processingJob */
        $processingJob = unserialize($serialized);
        $this->assertSame($processingJob->getJob()->getKey(), $fakeJob->getKey());
        $this->assertSame($processingJob->getJob()->getData(), $fakeJob->getData());
        $this->assertFalse($processingJob->IsHandlePayloadRemove());
        $this->assertSame($processingJob->getHeavyPayloadId(), $job->getHeavyPayloadId());

        $storedJob = HeavyJobsStore::get($job->getHeavyPayloadId());
        $this->assertInstanceOf(FakeJob::class, $storedJob);
    }

    public function testJobDestructorClearData(): void
    {
        $callback = function ($fakeJob) {
            $job = new HeavyJob($fakeJob, true);

            serialize($job);

            $this->assertNotEmpty(HeavyJobsStore::get($job->getHeavyPayloadId()));

            return $job->getHeavyPayloadId();
        };
        $payloadId = $callback(new FakeJob('key', [1, 2, 3]));

        $this->assertEmpty(HeavyJobsStore::get($payloadId));
    }
}
