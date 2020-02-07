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

namespace NavBundle\Tests\EntityListener;

use NavBundle\EntityListener\EntityListenerResolver;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityListenerResolverTest extends TestCase
{
    public function testItResolvesTheEntityListenerFromConstructorArguments(): void
    {
        $entityListener = new \stdClass();

        $resolver = new EntityListenerResolver([$entityListener]);
        $this->assertSame($entityListener, $resolver->resolve(\stdClass::class));
    }

    public function testItBuildsTheEntityListener(): void
    {
        $resolver = new EntityListenerResolver([]);
        $this->assertEquals(new \stdClass(), $resolver->resolve(\stdClass::class));
    }
}
