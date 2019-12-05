<?php

declare(strict_types=1);

namespace Umbrellio\LaravelHeavyJobs\Decorators;

use Closure;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Foundation\Application;
use Illuminate\Queue\QueueManager;

/**
 * @todo
 * До версии 6.0, у worker'ов указан тайп-хин QueueManager, по этому нельзя использовать обычный декоратор.
 * Когда пакет будет адапатирован под версию 6.0, можно будет использовать нормальный декоратор а не этот хак...
 *
 * @mixin QueueManager
 */
class QueueManagerDecorator extends QueueManager
{
    private $manger;

    public function __construct(QueueManager $manager, Application $app)
    {
        $this->manger = $manager;

        parent::__construct($app);
    }

    public function connection($name = null): Queue
    {
        $name = $name ?? $this->getDefaultDriver();
        if (!isset($this->connections[$name])) {
            $queue = $this->manger->connection($name);

            $this->connections[$name] = new QueueDecorator($queue);
        }

        return $this->connections[$name];
    }

    public function addConnector($driver, Closure $resolver)
    {
        $this->manger->addConnector($driver, $resolver);
    }
}
