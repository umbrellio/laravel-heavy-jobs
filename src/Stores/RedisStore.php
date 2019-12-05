<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Illuminate\Redis\RedisManager;

final class RedisStore implements StoreInterface
{
    private $connection;

    public function __construct(?string $connection, RedisManager $redis)
    {
        $this->connection = $redis->connection($connection);
    }

    public function get(string $id): ?string
    {
        return $this->doCommand('GET', $id);
    }

    public function set(string $id, string $serializedData): void
    {
        $this->doCommand('SET', $id, $serializedData);
    }

    public function has(string $id): bool
    {
        return (bool) $this->doCommand('EXISTS', $id);
    }

    public function remove(string $id): bool
    {
        return (bool) $this->doCommand('DEL', $id);
    }

    private function doCommand(string $command, string $id, ...$args)
    {
        $key = "heavy-jobs:{$id}";
        array_unshift($args, $key);

        return $this->connection->command($command, $args);
    }
}
