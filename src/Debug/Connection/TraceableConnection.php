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
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @method object Read(array $criteria)
 * @method object ReadMultiple(array $criteria)
 * @method object Create(array $criteria)
 * @method object Update(array $criteria)
 * @method object Delete(array $criteria)
 */
final class TraceableConnection implements ConnectionInterface, WarmableInterface
{
    /**
     * @var ConnectionInterface|\SoapClient
     */
    private $decorated;
    private $stopwatch;
    private $namespace;
    private $calls = [];

    public function __construct(ConnectionInterface $decorated, Stopwatch $stopwatch, string $namespace)
    {
        $this->decorated = $decorated;
        $this->stopwatch = $stopwatch;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function __call($functionName, $arguments)
    {
        $eventName = "$functionName($this->namespace)";

        $this->stopwatch->start($eventName, 'nav');
        try {
            return \call_user_func_array([$this->decorated, $functionName], $arguments);
        } catch (\SoapFault $fault) {
            throw $fault;
        } finally {
            $periods = $this->stopwatch->stop($eventName)->getPeriods();

            $this->calls[] = [
                'duration' => end($periods)->getDuration(),
                'memory' => end($periods)->getMemory(),
                'request' => $this->format($this->decorated->__getLastRequest()),
                'response' => $this->format($this->decorated->__getLastResponse()),
            ];
        }
    }

    public function getDuration(): float
    {
        $duration = 0.;
        foreach ($this->calls as $call) {
            $duration += $call['duration'];
        }

        return $duration;
    }

    public function count(): int
    {
        return \count($this->calls);
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): array
    {
        if ($this->decorated instanceof WarmableInterface) {
            return $this->decorated->warmUp($cacheDir);
        }

        return [];
    }

    private function format(?string $string): ?string
    {
        if (empty($string)) {
            return $string;
        }

        try {
            $doc = new \DOMDocument('1.0');
            $doc->loadXML($string);
            $doc->formatOutput = true;

            return $doc->saveXML();
        } catch (\Exception $e) {
            // Ignore error and return $string
        }

        return $string;
    }
}
