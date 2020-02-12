<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Debug\Connection;

use NavBundle\Connection\ConnectionInterface;
use NavBundle\Connection\ConnectionResolverInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TraceableConnectionResolver implements ConnectionResolverInterface
{
    private $decorated;
    private $stopwatch;
    private $connections = [];

    public function __construct(ConnectionResolverInterface $decorated, Stopwatch $stopwatch)
    {
        $this->decorated = $decorated;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($className, $namespace): ConnectionInterface
    {
        if (isset($this->connections[$namespace])) {
            return $this->connections[$namespace];
        }

        $eventName = "fetchWSDL($namespace)";
        $this->stopwatch->start($eventName, 'nav');
        $parentConnection = $this->decorated->resolve($className, $namespace);
        $this->stopwatch->stop($eventName);

        return $this->connections[$namespace] = new TraceableConnection($parentConnection, $this->stopwatch, $namespace);
    }
}
