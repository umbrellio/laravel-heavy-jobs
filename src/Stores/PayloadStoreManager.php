<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Illuminate\Support\Str;
use Umbrellio\LaravelHeavyJobs\Exceptions\UuidCollisionException;

class PayloadStoreManager
{
    private $store;

    public function __construct(StoreResolver $storeResolver)
    {
        $this->store = $storeResolver->resolve();
    }

    public function store(string $identifier, $job): void
    {
        $this->store->set($identifier, serialize($job));
    }

    public function get(string $identifier)
    {
        if (($serialized = $this->store->get($identifier)) === null) {
            return [];
        }

        return unserialize($serialized);
    }

    public function remove(string $identifier): void
    {
        $this->store->remove($identifier);
    }

    public function generateId(): string
    {
        $identifier = Str::uuid()->toString();
        if ($this->store->has($identifier)) {
            throw new UuidCollisionException('This uuid already used.');
        }

        return $identifier;
    }
}
