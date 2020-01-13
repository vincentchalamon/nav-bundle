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

namespace NavBundle\Connection;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ConnectionResolver implements ConnectionResolverInterface
{
    private $wsdl;
    private $options;
    private $connections = [];

    public function __construct(string $wsdl, array $options, iterable $connections)
    {
        $this->wsdl = $wsdl;
        $this->options = $options;
        foreach ($connections as $connection) {
            $this->connections[\get_class($connection)] = $connection;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function resolve($className, $namespace): object
    {
        if (isset($this->connections[$className = trim($className, '\\')])) {
            return $this->connections[$className];
        }

        return $this->connections[$className] = new Connection($this->wsdl.$namespace, $this->options);
    }
}
