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

use NavBundle\Debug\Manager\TraceableManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavDataCollector extends DataCollector
{
    private $managers;

    /**
     * @param iterable|TraceableManager[]
     */
    public function __construct(iterable $managers)
    {
        $this->managers = $managers;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $this->data = [
            'count' => 0,
            'duration' => 0.,
            'memory' => 0.,
            'managers' => [],
        ];

        foreach ($this->managers as $name => $manager) {
            $this->data['managers'][$name] = [
                'calls' => $manager->getCalls(),
                'duration' => $manager->getDuration(),
                'memory' => $manager->getMemory(),
                'count' => $manager->count(),
            ];

            $this->data['duration'] += $this->data['managers'][$name]['duration'];
            $this->data['memory'] += $this->data['managers'][$name]['memory'];
            $this->data['count'] += $this->data['managers'][$name]['count'];
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
     * @return TraceableManager[]
     */
    public function getManagers(): array
    {
        return $this->data['managers'];
    }
}
