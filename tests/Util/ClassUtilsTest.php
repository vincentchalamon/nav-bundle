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

namespace NavBundle\Tests\Util;

use NavBundle\Util\ClassUtils;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassUtilsTest extends TestCase
{
    public function testItGetsRealClassFromProxyObject(): void
    {
        $holderFactory = new LazyLoadingValueHolderFactory();
        $proxy = $holderFactory->createProxy(\stdClass::class, static function (): void {});

        $this->assertEquals(\stdClass::class, ClassUtils::getRealClass($proxy));
    }

    public function testItGetsRealClassFromProxyClass(): void
    {
        $this->assertEquals(
            \stdClass::class,
            ClassUtils::getRealClass('ProxyManagerGeneratedProxy\__PM__\stdClass\Generated5c74158c77afab06c2df7ffd2ce85029')
        );
    }

    public function testItDoesNotChangeClassIfItIsNotAProxy(): void
    {
        $this->assertEquals(\stdClass::class, ClassUtils::getRealClass(\stdClass::class));
    }
}
