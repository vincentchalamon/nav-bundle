<?php

declare(strict_types=1);

namespace NavBundle\Debug\Connection;

use NavBundle\Connection\ConnectionInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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
    private $calls = [];

    public function __construct(ConnectionInterface $decorated, Stopwatch $stopwatch)
    {
        $this->decorated = $decorated;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function __call($functionName, $arguments)
    {
        $this->stopwatch->start($functionName, 'nav');

        $response = call_user_func_array([$this->decorated, $functionName], $arguments);

        $event = $this->stopwatch->stop($functionName);
        $this->calls[] = [
            'event' => $event,
            'request' => $this->format($this->decorated->__getLastRequest()),
            'response' => $this->format($this->decorated->__getLastResponse()),
        ];

        return $response;
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

    public function warmUp($cacheDir)
    {
        if ($this->decorated instanceof WarmableInterface) {
            $this->decorated->warmUp($cacheDir);
        }
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