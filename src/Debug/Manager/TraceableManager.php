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

use NavBundle\ClassMetadata\Driver\ClassMetadataDriverInterface;
use NavBundle\Manager\Manager;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
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
        ClassMetadataDriverInterface $driver,
        SerializerInterface $serializer,
        ConfigCacheFactoryInterface $configCacheFactory,
        \Traversable $repositories,
        \Traversable $clients,
        string $wsdl,
        array $options,
        array $soapOptions,
        string $cacheDir,
        Stopwatch $stopwatch
    ) {
        parent::__construct($driver, $serializer, $configCacheFactory, $repositories, $clients, $wsdl, $options, $soapOptions, $cacheDir);
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $className, string $id)
    {
        $this->stopwatch->start('nav.find');
        $result = parent::find($className, $id);
        $this->registerCall($this->stopwatch->stop('nav.find'), $this->getClient($className));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $className)
    {
        $this->stopwatch->start('nav.findAll');
        $results = parent::findAll($className);
        $this->registerCall($this->stopwatch->stop('nav.findAll'), $this->getClient($className));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $className, array $criteria = [], int $size = 0)
    {
        $this->stopwatch->start('nav.findBy');
        $results = parent::findBy($className, $criteria, $size);
        $this->registerCall($this->stopwatch->stop('nav.findBy'), $this->getClient($className));

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
