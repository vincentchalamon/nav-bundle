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

use NavBundle\Connection\ConnectionResolver;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ConnectionResolverTest extends TestCase
{
    public function testItResolvesConnection(): void
    {
        $resolver = new ConnectionResolver('https://user:password@www.example.com', []);
        $connection = $resolver->resolve(\stdClass::class, 'FOO');

        $this->assertInstanceOf(\stdClass::class, $connection);
        $this->assertSame($connection, $resolver->resolve(\stdClass::class, 'FOO'));
    }
}
