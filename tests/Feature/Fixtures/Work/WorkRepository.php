<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature\Fixtures\Work;

final class WorkRepository
{
    private $works = [];

    public function getWork(string $workName): Work
    {
        if (empty($this->works[$workName])) {
            $this->works[$workName] = new Work();
        }

        return $this->works[$workName];
    }
}
