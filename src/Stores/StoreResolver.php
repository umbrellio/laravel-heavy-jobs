<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Stores;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

final class StoreResolver
{
    private $app;
    private $store;
    private $customDrivers = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function resolve(): StoreInterface
    {
        return $this->store ?? $this->store = $this->getStore();
    }

    public function extend(string $name, Closure $callback): void
    {
        $this->customDrivers[$name] = $callback;
    }

    private function getStore(): StoreInterface
    {
        $driver = $this->app['config']['heavy-jobs']['driver'];
        $parameters = $this->app['config']['heavy-jobs']['parameters'];

        if (isset($this->customDrivers[$driver])) {
            $store = $this->customDrivers[$driver]($this->app, $parameters);
        } else {
            $class = sprintf('Umbrellio\\LaravelHeavyJobs\\Stores\\%sStore', ucfirst($driver));
            if (!class_exists($class)) {
                throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
            }

            $store = $this->app->make($class, $parameters);
        }

        if (!$store instanceof StoreInterface) {
            throw new InvalidArgumentException(sprintf(
                'Store "%s" must be implements StoreInterface.',
                get_class($store)
            ));
        }

        return $store;
    }
}
