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

namespace NavBundle\Debug\DataCollector;

use NavBundle\Debug\Connection\TraceableConnection;
use NavBundle\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavDataCollector extends DataCollector
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $this->data = [
            'count' => 0,
            'duration' => 0.,
            'memory' => 0.,
            'connections' => [],
        ];

        foreach ($this->registry->getConnections() as $name => $connection) {
            $this->data['connections'][$name] = [
                'calls' => $connection->getCalls(),
                'duration' => $connection->getDuration(),
                'memory' => $connection->getMemory(),
                'count' => $connection->count(),
            ];

            $this->data['duration'] += $this->data['connections'][$name]['duration'];
            $this->data['memory'] += $this->data['connections'][$name]['memory'];
            $this->data['count'] += $this->data['connections'][$name]['count'];
        }
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'nav';
    }

    public function count(): int
    {
        return (int) $this->data['count'];
    }

    public function getDuration(): float
    {
        return (float) $this->data['duration'];
    }

    public function getMemory(): float
    {
        return (float) $this->data['memory'];
    }

    /**
     * @return TraceableConnection[]
     */
    public function getConnections(): array
    {
        return $this->data['connections'];
    }
}
