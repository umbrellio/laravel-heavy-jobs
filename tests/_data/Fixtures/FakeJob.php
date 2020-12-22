<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RuntimeException;
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Umbrellio\LaravelHeavyJobs\Tests\_data\Fixtures\Work\WorkRepository;

final class FakeJob implements ShouldQueue, ShouldStorePayload
{
    use Queueable;
    use Dispatchable;

    private $workName;

    public function __construct(string $workName)
    {
        $this->workName = $workName;
    }

    public function handle(WorkRepository $workRepository): void
    {
        $work = $workRepository->getWork($this->workName);
        if ($work->isCompleted()) {
            throw new RuntimeException('Work already completed!');
        }

        $work->doWork();
    }

    public function getWorkName(): string
    {
        return $this->workName;
    }
}
