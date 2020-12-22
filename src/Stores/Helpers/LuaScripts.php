<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores\Helpers;

final class LuaScripts
{
    /**
     * Возвращает сохраненный payload.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища неудавшихся job'ок.
     * ARGV[1] - id payload'a.
     *
     * @return string сохраненный payload job'ки.
     */
    public static function get()
    {
        return <<<'CODE_SAMPLE'
local failed_payload = redis.call('hget', KEYS[2], ARGV[1])
if(failed_payload ~= false) then
    redis.call('hset', KEYS[1], ARGV[1], failed_payload)
end

return redis.call('hget', KEYS[1], ARGV[1])
CODE_SAMPLE;
    }

    /**
     * Перемещает payload в хеш-хранилище неудавшихся job'ок.
     *
     * KEYS[1] - ключ хеш-хранилища job'ок.
     * KEYS[2] - ключ хеш-хранилища неудавшихся job'ок.
     * ARGV[1] - id payload'а.
     *
     * @return int 1 в случае успешного перемещения job'ки, иначе 0.
     */
    public static function markAsFailed()
    {
        return <<<'CODE_SAMPLE'
local payload = redis.call('hget', KEYS[1], ARGV[1])
local marked = 0

if(payload ~= false) then
    marked = redis.call('hset', KEYS[2], ARGV[1], payload)
    redis.call('hdel', KEYS[1], ARGV[1])
end

return marked
CODE_SAMPLE;
    }
}
