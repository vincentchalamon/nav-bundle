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
    private const URL_PATTERN = '#^(https?)://([^:]+):([^@]+)@(.*)$#';

    private $className;
    private $wsdl;
    private $options;
    private $connections = [];

    public function __construct(string $className, string $url, array $options)
    {
        $this->className = $className;

        if (!preg_match(self::URL_PATTERN, $url, $matches)) {
            throw new \InvalidArgumentException('Malformed parameter "url".');
        }

        $this->wsdl = $matches[1].'://'.$matches[4];
        $options['user'] = $matches[2];
        $options['password'] = $matches[3];

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function resolve($namespace): object
    {
        if (isset($this->connections[$namespace])) {
            return $this->connections[$namespace];
        }
        $className = $this->className;

        return $this->connections[$namespace] = new $className($this->wsdl.$namespace, $this->options);
    }
}
