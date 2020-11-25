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

namespace NavBundle\Tests\Connection;

use NavBundle\Connection\ConnectionInterface;
use NavBundle\Connection\ConnectionResolver;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ConnectionResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testItResolvesConnection(): void
    {
        $connectionClass = \get_class($this->prophesize(ConnectionInterface::class)->reveal());

        $resolver = new ConnectionResolver('https://user:password@www.example.com', []);
        $connection = $resolver->resolve($connectionClass, 'FOO');

        $this->assertInstanceOf($connectionClass, $connection);
        $this->assertSame($connection, $resolver->resolve($connectionClass, 'FOO'));
    }
}
