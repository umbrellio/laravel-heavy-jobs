<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Jobs;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;

final class HeavyJob
{
    private $job;
    private $heavyPayloadId;
    private $handlePayloadRemove;

    public function __construct($job, $handlePayloadRemove = false)
    {
        $this->job = $job;
        $this->handlePayloadRemove = $handlePayloadRemove;
        $this->heavyPayloadId = HeavyJobsStore::generateId();
    }

    public function __destruct()
    {
        if ($this->handlePayloadRemove) {
            HeavyJobsStore::remove($this->heavyPayloadId);
        }
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getHeavyPayloadId(): string
    {
        return $this->heavyPayloadId;
    }

    public function IsHandlePayloadRemove(): bool
    {
        return $this->handlePayloadRemove;
    }

    public function DisablePayloadRemove(): void
    {
        $this->handlePayloadRemove = false;
    }

    public function handle(QueueingDispatcher $dispatcher)
    {
        return $dispatcher->dispatchNow($this->job);
    }

    public function displayName(): string
    {
        if (method_exists($this->job, 'displayName')) {
            return $this->job->displayName();
        }

        return get_class($this->job);
    }

    public function failed()
    {
        HeavyJobsStore::markAsfailed($this->heavyPayloadId);
    }

    public function __get($name)
    {
        return $this->job->{$name} ?? null;
    }

    public function __set($name, $value): void
    {
        $this->job->{$name}($value);
    }

    public function __sleep(): array
    {
        HeavyJobsStore::store($this->heavyPayloadId, $this->job);

        return ['heavyPayloadId'];
    }

    public function __wakeup(): void
    {
        $this->job = HeavyJobsStore::get($this->heavyPayloadId);
        $this->handlePayloadRemove = false;
    }

    public function __clone()
    {
        $this->handlePayloadRemove = false;
    }

    public function __call($name, $arguments)
    {
        return $this->job->{$name}(...$arguments);
    }
}
