<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Tests\Feature;

use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Umbrellio\LaravelHeavyJobs\Stores\RedisStore;
use Umbrellio\LaravelHeavyJobs\Stores\StoreInterface;
use Umbrellio\LaravelHeavyJobs\Stores\StoreResolver;
use Umbrellio\LaravelHeavyJobs\Tests\IntegrationTest;

class StoreResolverTest extends IntegrationTest
{
    /** @var StoreResolver */
    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->app->make(StoreResolver::class);
    }

    public function testResolveRedis(): void
    {
        Redis::spy()
            ->shouldReceive('connection')
            ->once()
            ->andReturn(Mockery::mock(RedisConnection::class));

        $this->assertInstanceOf(RedisStore::class, $this->resolver->resolve());
    }

    public function testResolveCustomDriver(): void
    {
        $this->app['config']->set('heavy-jobs.driver', 'custom');

        $customStore = Mockery::mock(StoreInterface::class);
        $this->resolver->extend('custom', function () use ($customStore) {
            return $customStore;
        });

        $this->assertSame($customStore, $this->resolver->resolve());
    }

    public function testResolveStoreCache(): void
    {
        $this->app['config']->set('heavy-jobs.driver', 'custom');
        $customStore = Mockery::mock(StoreInterface::class);
        $this->resolver->extend('custom', function () use ($customStore) {
            return $customStore;
        });

        $this->assertSame($customStore, $this->resolver->resolve());
        $this->assertSame($customStore, $this->resolver->resolve());
    }
}
