<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Illuminate\Redis\RedisManager;
use Umbrellio\LaravelHeavyJobs\Stores\Helpers\LuaScripts;

final class RedisStore implements StoreInterface
{
    public const JOBS_HASH_KEY = 'heavy_job_payloads';
    public const FAILED_JOBS_HASH_KEY = 'failed_heavy_job_payloads';

    private $connection;

    public function __construct(?string $connection, RedisManager $redis)
    {
        $this->connection = $redis->connection($connection);
    }

    public function get(string $id): ?string
    {
        return $this->connection->eval(
            LuaScripts::get(), 2, self::JOBS_HASH_KEY, self::FAILED_JOBS_HASH_KEY, $id
        ) ?: null;
    }

    public function set(string $id, string $serializedData): bool
    {
        return (bool)$this->connection->eval(
            LuaScripts::set(), 2, self::JOBS_HASH_KEY, self::FAILED_JOBS_HASH_KEY, $id, $serializedData
        );
    }

    public function has(string $id): bool
    {
        return (bool)$this->connection->eval(
            LuaScripts::has(), 2, self::JOBS_HASH_KEY, self::FAILED_JOBS_HASH_KEY, $id
        );
    }

    public function remove(string $id): bool
    {
        return (bool)$this->connection->eval(
            LuaScripts::remove(), 2, self::JOBS_HASH_KEY, self::FAILED_JOBS_HASH_KEY, $id
        );
    }

    public function markAsFailed(string $id): bool
    {
        return (bool)$this->connection->eval(
            LuaScripts::markAsFailed(), 2, self::JOBS_HASH_KEY, self::FAILED_JOBS_HASH_KEY, $id
        );
    }

    public function flushFailed(): bool
    {
        return (bool)$this->connection->del(self::FAILED_JOBS_HASH_KEY);
    }
}
