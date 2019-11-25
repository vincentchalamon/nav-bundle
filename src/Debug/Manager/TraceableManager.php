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

namespace NavBundle\Debug\Manager;

use NavBundle\Manager\Manager;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TraceableManager extends Manager
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;
    private $calls = [];

    public function setStopwatch(Stopwatch $stopwatch): void
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $className, string $id): ?object
    {
        $this->stopwatch->start('nav.find');
        $result = parent::find($className, $id);
        $this->registerCall($this->stopwatch->stop('nav.find'), $this->getClient($className));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $className): \Generator
    {
        $this->stopwatch->start('nav.findAll');
        yield parent::findAll($className);
        $this->registerCall($this->stopwatch->stop('nav.findAll'), $this->getClient($className));
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $className, array $criteria = [], int $size = 0): \Generator
    {
        $this->stopwatch->start('nav.findBy');
        yield parent::findBy($className, $criteria, $size);
        $this->registerCall($this->stopwatch->stop('nav.findBy'), $this->getClient($className));
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(string $className, array $criteria = []): ?object
    {
        $this->stopwatch->start('nav.findOneBy');
        $result = parent::findOneBy($className, $criteria);
        $this->registerCall($this->stopwatch->stop('nav.findOneBy'), $this->getClient($className));

        return $result;
    }

    public function getDuration(): float
    {
        $duration = 0.;
        foreach ($this->calls as $call) {
            $duration += $call['event']->getDuration();
        }

        return $duration;
    }

    public function getMemory(): float
    {
        $memory = 0.;
        foreach ($this->calls as $call) {
            $memory += $call['event']->getMemory();
        }

        return $memory;
    }

    public function count(): int
    {
        return \count($this->calls);
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    private function registerCall(StopwatchEvent $event, \SoapClient $client): void
    {
        $this->calls[] = [
            'event' => $event,
            'request' => $this->format($client->__getLastRequest()),
            'response' => $this->format($client->__getLastResponse()),
        ];
    }

    private function format(string $string): string
    {
        try {
            $doc = new \DOMDocument('1.0');
            $doc->loadXML($string);
            $doc->formatOutput = true;

            return $doc->saveXML();
        } catch (\Exception $e) {
        }

        return $string;
    }
}
