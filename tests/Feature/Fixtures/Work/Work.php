<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\Work;

final class Work
{
    private $completed = false;

    public function doWork(): void
    {
        $this->completed = true;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }
}
