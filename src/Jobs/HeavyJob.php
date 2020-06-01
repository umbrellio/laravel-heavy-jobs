<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Jobs;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;

final class HeavyJob
{
    private $job;
    private $heavyPayloadId;
    private $isPushed;

    public function __construct($job)
    {
        $this->job = $job;
        $this->heavyPayloadId = HeavyJobsStore::generateId();
        $this->isPushed = false;
    }

    public function __destruct()
    {
        if (!$this->isPushed) {
            HeavyJobsStore::remove($this->heavyPayloadId);
        }
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

    public function pushed(): void
    {
        $this->isPushed = true;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getHeavyPayloadId(): string
    {
        return $this->heavyPayloadId;
    }

    public function isPushed(): bool
    {
        return $this->isPushed;
    }

    public function __clone()
    {
        $this->pushed();
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
        $this->isPushed = true;
    }

    public function __call($name, $arguments)
    {
        return $this->job->{$name}(...$arguments);
    }
}
