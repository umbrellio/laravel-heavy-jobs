<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Decorators;

use Illuminate\Contracts\Queue\Queue;
use Umbrellio\LaravelHeavyJobs\Jobs\HeavyJob;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;

final class QueueDecorator implements Queue
{
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function __call($name, $arguments)
    {
        return $this->queue->{$name}(...$arguments);
    }

    public function size($queue = null)
    {
        return $this->queue->size();
    }

    public function push($job, $data = '', $queue = null)
    {
        $job = $this->prepareJob($job);

        return tap($this->queue->push($job, $data, $queue), function () use ($job): void {
            $this->markJobs([$job]);
        });
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->queue->pushRaw($payload, $queue, $options);
    }

    public function pushOn($queue, $job, $data = '')
    {
        $job = $this->prepareJob($job);

        return tap($this->queue->pushOn($queue, $job, $data), function () use ($job): void {
            $this->markJobs([$job]);
        });
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        $job = $this->prepareJob($job);

        return tap($this->queue->later($delay, $job, $data, $queue), function () use ($job): void {
            $this->markJobs([$job]);
        });
    }

    public function laterOn($queue, $delay, $job, $data = '')
    {
        $job = $this->prepareJob($job);

        return tap($this->queue->laterOn($queue, $delay, $job, $data), function () use ($job): void {
            $this->markJobs([$job]);
        });
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        $jobs = $this->prepareJobs($jobs);

        return tap($this->queue->bulk($jobs, $data, $queue), function () use ($jobs): void {
            $this->markJobs($jobs);
        });
    }

    public function pop($queue = null)
    {
        return $this->queue->pop($queue);
    }

    public function getConnectionName(): string
    {
        return $this->queue->getConnectionName();
    }

    public function setConnectionName($name): self
    {
        $this->queue->setConnectionName($name);

        return $this;
    }

    private function prepareJob($job)
    {
        if (
            $job instanceof ShouldStorePayload &&
            (!method_exists($job, 'isHeavyJobsEnabled') || $job->isHeavyJobsEnabled())
        ) {
            return new HeavyJob($job);
        }

        return $job;
    }

    private function markJobs(array $jobs): void
    {
        foreach ($jobs as $job) {
            if ($job instanceof HeavyJob) {
                $job->pushed();
            }
        }
    }

    private function prepareJobs($jobs): array
    {
        return array_map(function ($job) {
            return $this->prepareJob($job);
        }, $jobs);
    }
}
