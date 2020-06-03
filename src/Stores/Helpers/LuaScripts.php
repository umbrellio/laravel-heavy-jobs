<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores\Helpers;

final class LuaScripts
{
    /**
     * Возвращает сохраненный payload.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища job'ок завершившихся ошибкой.
     * ARGV[1] - id payload'a
     *
     * @return string
     */
    public static function get()
    {
        return <<<'LUA'
local failed_payload = redis.call('hget', KEYS[2], ARGV[1])
if(failed_payload ~= false) then
    redis.call('hset', KEYS[1], ARGV[1], failed_payload)
end

return redis.call('hget', KEYS[1], ARGV[1])
LUA;
    }

    /**
     * Перемещает payload в хеш-хранилище job'ок завершившихся ошибкой.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища job'ок завершившихся ошибкой.
     * ARGV[1] - id payload'а
     *
     * @return string
     */
    public static function markAsFailed()
    {
        return <<<'LUA'
local payload = redis.call('hget', KEYS[1], ARGV[1])
local marked = 0

if(payload ~= false) then
    marked = redis.call('hset', KEYS[2], ARGV[1], payload)
    redis.call('hdel', KEYS[1], ARGV[1])
end

return marked
LUA;
    }
}
