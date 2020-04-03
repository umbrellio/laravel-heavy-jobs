<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;

final class FakeJob implements ShouldQueue, ShouldStorePayload
{
    use Queueable;
    use Dispatchable;

    private $key;
    private $data;

    public function __construct(string $key, array $data)
    {
        $this->key = $key;
        $this->data = $data;
    }

    public function handle(): void
    {
        Cache::forever($this->key, $this->data);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
