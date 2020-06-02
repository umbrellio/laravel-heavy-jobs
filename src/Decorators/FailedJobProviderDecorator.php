<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Decorators;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Arr;
use Umbrellio\LaravelHeavyJobs\Facades\HeavyJobsStore;

final class FailedJobProviderDecorator implements FailedJobProviderInterface
{
    private $failedJobProvider;

    public function __construct(FailedJobProviderInterface $failedJobProvider)
    {
        $this->failedJobProvider = $failedJobProvider;
    }

    public function log($connection, $queue, $payload, $exception)
    {
        return $this->failedJobProvider->log($connection, $queue, $payload, $exception);
    }

    public function all()
    {
        return $this->failedJobProvider->all();
    }

    public function find($id)
    {
        return $this->failedJobProvider->find($id);
    }

    public function forget($id)
    {
        if ($record = $this->failedJobProvider->find($id)) {
            $heavyPayloadId = Arr::get(json_decode($record->payload, true), 'heavy-payload-id');
            if ($heavyPayloadId) {
                HeavyJobsStore::remove($heavyPayloadId);
            }
        }

        return $this->failedJobProvider->forget($id);
    }

    public function flush()
    {
        HeavyJobsStore::flushFailed();

        $this->failedJobProvider->flush();
    }

    public function __call($name, $arguments)
    {
        return $this->failedJobProvider->{$name}(...$arguments);
    }
}
