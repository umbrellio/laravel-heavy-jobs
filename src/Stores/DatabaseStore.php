<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Illuminate\Database\DatabaseManager;

final class DatabaseStore implements StoreInterface
{
    private $connection;

    public function __construct(?string $connection, DatabaseManager $databaseManager)
    {
        $this->connection = $databaseManager->connection($connection);
    }

    public function get(string $id): ?string
    {
        return $this->getPayload($id);
    }

    public function set(string $id, string $serializedData): void
    {
        $this->connection->table('heavy_jobs')->insert([
            'id' => $id,
            'payload' => $serializedData,
        ]);
    }

    public function has(string $id): bool
    {
        return $this->getPayload($id) !== null;
    }

    public function remove(string $id): bool
    {
        return $this->connection->table('heavy_jobs')->delete($id) > 0;
    }

    private function getPayload(string $id): ?string
    {
        return $this->connection->table('heavy_jobs')
            ->where('id', $id)
            ->limit(1)
            ->value('payload');
    }
}
