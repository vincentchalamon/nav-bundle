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

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Manager\Manager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TraceableManager extends Manager
{
    private $stopwatch;
    private $calls = [];

    public function __construct(
        ClassMetadataInterface $classMetadata,
        SerializerInterface $serializer,
        PropertyAccessorInterface $propertyAccessor,
        \Traversable $repositories,
        \Traversable $clients,
        string $wsdl,
        array $options,
        array $soapOptions,
        Stopwatch $stopwatch
    ) {
        parent::__construct($classMetadata, $serializer, $propertyAccessor, $repositories, $clients, $wsdl, $options, $soapOptions);
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $class, string $id)
    {
        $this->stopwatch->start('nav.find');
        $result = parent::find($class, $id);
        $this->registerCall($this->stopwatch->stop('nav.find'), $this->getClient($class));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $class)
    {
        $this->stopwatch->start('nav.findAll');
        $results = parent::findAll($class);
        $this->registerCall($this->stopwatch->stop('nav.findAll'), $this->getClient($class));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $class, array $criteria = [], int $size = 0)
    {
        $this->stopwatch->start('nav.findBy');
        $results = parent::findBy($class, $criteria, $size);
        $this->registerCall($this->stopwatch->stop('nav.findBy'), $this->getClient($class));

        return $results;
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
