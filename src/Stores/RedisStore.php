<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Carbon\Carbon;
use Illuminate\Redis\RedisManager;
use Umbrellio\LaravelHeavyJobs\Stores\Helpers\LuaScripts;
use function \count;

final class RedisStore implements StoreInterface
{
    public const JOB_PAYLOADS_KEY = 'heavy_job_payloads';
    public const FAILED_JOB_PAYLOADS_KEY = 'failed_heavy_job_payloads';
    public const LIFETIME_FAILED_JOB_PAYLOADS_KEY = 'lifetime_failed_heavy_job_payloads';

    private $connection;
    private $lifetime;

    public function __construct(?string $connection, RedisManager $redis)
    {
        $this->connection = $redis->connection($connection);
        $this->lifetime = config('heavy-jobs.failed_job_lifetime', -1);
    }

    public function get(string $id): ?string
    {
        $score = $this->lifetime !== -1 ? Carbon::now()->getTimestamp() : 0;

        [$payload, $ids] = $this->connection->pipeline(function ($pipe) use ($id, $score) {
            $pipe->eval(LuaScripts::get(), [self::JOB_PAYLOADS_KEY, self::FAILED_JOB_PAYLOADS_KEY, $id], 2);
            $pipe->zrangebyscore(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY, '-inf', (string)$score);
        });

        if (count($ids)) {
            $this->connection->pipeline(function ($pipe) use ($ids, $score) {
                $pipe->hdel(self::FAILED_JOB_PAYLOADS_KEY, ...$ids);
                $pipe->zremrangebyscore(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY, '-inf', (string)$score);
            });
        }

        return $payload ?: null;
    }

    public function getFailed(string $id): ?string
    {
        return $this->connection->hget(self::FAILED_JOB_PAYLOADS_KEY, $id) ?: null;
    }

    public function set(string $id, string $serializedData): bool
    {
        [$set] = $this->connection->pipeline(function ($pipe) use ($id, $serializedData) {
            $pipe->hset(self::JOB_PAYLOADS_KEY, $id, $serializedData);
            $pipe->zrem(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY, $id);
        });

        return (bool) $set;
    }

    public function has(string $id): bool
    {
        return (bool) $this->connection->hexists(self::JOB_PAYLOADS_KEY, $id);
    }

    public function remove(string $id): bool
    {
        return (bool) $this->connection->hdel(self::JOB_PAYLOADS_KEY, $id);
    }

    public function removeFailed(string $id): bool
    {
        [$deleted] = $this->connection->pipeline(function ($pipe) use ($id) {
            $pipe->hdel(self::FAILED_JOB_PAYLOADS_KEY, $id);
            $pipe->zrem(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY, $id);
        });

        return (bool) $deleted;
    }

    public function markAsFailed(string $id): bool
    {
        [$marked] = $this->connection->pipeline(function ($pipe) use ($id) {
            $pipe->eval(LuaScripts::markAsFailed(), [self::JOB_PAYLOADS_KEY, self::FAILED_JOB_PAYLOADS_KEY, $id], 2);
            $pipe->zadd(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY, Carbon::now()->getTimestamp() + $this->lifetime, $id);
        });

        return (bool) $marked;
    }

    public function flushFailed(): bool
    {
        $result = $this->connection->pipeline(function ($pipe) {
            $pipe->del(self::FAILED_JOB_PAYLOADS_KEY);
            $pipe->del(self::LIFETIME_FAILED_JOB_PAYLOADS_KEY);
        });

        return !empty($result[0]) && !empty($result[1]);
    }
}
