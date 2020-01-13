<?php

declare(strict_types=1);

namespace NavBundle\Debug\Connection;

use NavBundle\Connection\ConnectionInterface;
use NavBundle\Connection\ConnectionResolverInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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
        if (isset($this->connections[$className = trim($className, '\\')])) {
            return $this->connections[$className];
        }

        $this->stopwatch->start('fetch WSDL', 'nav');
        $parentConnection = $this->decorated->resolve($className, $namespace);
        $this->stopwatch->stop('fetch WSDL');

        return $this->connections[$className] = new TraceableConnection($parentConnection, $this->stopwatch);
    }
}