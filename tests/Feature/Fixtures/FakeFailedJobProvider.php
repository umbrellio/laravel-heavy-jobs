<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use \StdClass;

final class FakeFailedJobProvider implements FailedJobProviderInterface
{
    private $records = [];

    public function log($connection, $queue, $payload, $exception)
    {
        $record = new StdClass;
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

    public function forget($id)
    {
        unset($this->records[$id]);
    }

    public function flush()
    {
        $this->records = [];
    }
}
