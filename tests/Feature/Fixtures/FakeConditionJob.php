<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures;

use Illuminate\Contracts\Queue\ShouldQueue;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;

final class FakeConditionJob implements ShouldQueue, ShouldStorePayload
{
    private $isHeavyJobEnabled;

    public function __construct(bool $isHeavyJobEnabled)
    {
        $this->isHeavyJobEnabled = $isHeavyJobEnabled;
    }

    public function isHeavyJobsEnabled(): bool
    {
        return $this->isHeavyJobEnabled;
    }
}
