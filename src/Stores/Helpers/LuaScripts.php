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
local payload = redis.call('hget', KEYS[1], ARGV[1])

if(payload == false) then
    payload = redis.call('hget', KEYS[2], ARGV[1])
end

return payload
LUA;
    }

    /**
     * Записывает payload во временное хеш-хранилище.
     *
     * KEYS[1] - ключ временного хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища job'ок завершившихся ошибкой.
     * ARGV[1] - id payload'a
     * ARGV[2] - payload
     *
     * @return string
     */
    public static function set()
    {
        return <<<'LUA'
redis.call('hdel', KEYS[2], ARGV[1])

return redis.call('hset', KEYS[1], ARGV[1], ARGV[2])
LUA;
    }

    /**
     * Проверяет существование payload в хеш-хранилище.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища job'ок завершившихся ошибкой.
     * ARGV[1] - id payload'а
     *
     * @return string
     */
    public static function has()
    {
        return <<<'LUA'
local exists = redis.call('hexists', KEYS[1], ARGV[1])

if(exists == 0) then
    exists = redis.call('hexists', KEYS[2], ARGV[1])
end

return exists
LUA;
    }

    /**
     * Удаляет payload из хеш-хранилище.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища job'ок завершившихся ошибкой.
     * ARGV[1] - id payload'а
     *
     * @return string
     */
    public static function remove()
    {
        return <<<'LUA'
local deleted = redis.call('hdel', KEYS[1], ARGV[1])

if(deleted == 0) then
    deleted = redis.call('hdel', KEYS[2], ARGV[1])
end

return deleted
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
end

return marked
LUA;
    }
}
