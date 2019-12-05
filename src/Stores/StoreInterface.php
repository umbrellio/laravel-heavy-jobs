<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

interface StoreInterface
{
    public function get(string $id): ?string;

    public function set(string $id, string $serializedData): void;

    public function has(string $id): bool;

    public function remove(string $id): bool;
}
