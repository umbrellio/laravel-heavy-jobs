<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RuntimeException;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;

final class FakeFailedJob implements ShouldQueue, ShouldStorePayload
{
    use Queueable;
    use Dispatchable;

    public function handle(): void
    {
        throw new RuntimeException('Some exception.');
    }
}
