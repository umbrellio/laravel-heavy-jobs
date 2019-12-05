<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class DatabaseStoreCommand extends Command
{
    protected $name = 'heavy-jobs:db-store-table';
    protected $description = 'Create a migration for the queue payload database table';
    protected $files;
    protected $composer;

    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    public function handle(): void
    {
        $this->files->put(
            $this->createBaseMigration(),
            $this->files->get(__DIR__ . '/stubs/create-heavy-jobs-table.stub')
        );

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    protected function createBaseMigration(): string
    {
        $path = $this->laravel->databasePath('migrations');

        return $this->laravel['migration.creator']->create('create_heavy_jobs_table', $path);
    }
}
