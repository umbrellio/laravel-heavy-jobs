<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use StdClass;

final class FakeFailedJobProvider implements FailedJobProviderInterface
{
    private $records = [];

    public function log($connection, $queue, $payload, $exception)
    {
        $record = new StdClass();
        $record->connection = $connection;
        $record->queue = $queue;
        $record->payload = $payload;
        $record->exception = $exception;

        $this->records[] = $record;

        return count($this->records) - 1;
    }

    public function all()
    {
        return $this->records;
    }

    public function find($id)
    {
        return $this->records[$id] ?? null;
    }

    public function forget($id): bool
    {
        unset($this->records[$id]);

        return !array_keys($this->records, $id, true);
    }

    public function flush($hours = null): void
    {
        $this->records = [];
    }

    public function ids($queue = null)
    {
        return array_keys($this->records);
    }
}
