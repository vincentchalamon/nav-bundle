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

    private $wsdl;
    private $options;
    private $connections = [];

    public function __construct(string $url, array $options = [])
    {
        if (!preg_match(self::URL_PATTERN, $url, $matches)) {
            throw new \InvalidArgumentException('Malformed parameter "url".');
        }

        $this->wsdl = rtrim($matches[1].'://'.$matches[4], '/').'/';
        $options['user'] = $matches[2];
        $options['password'] = $matches[3];

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $className, string $namespace): ConnectionInterface
    {
        if (isset($this->connections[$namespace])) {
            return $this->connections[$namespace];
        }

        return $this->connections[$namespace] = new $className($this->wsdl.$namespace, $this->options);
    }
}
