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

namespace NavBundle\Tests;

use NavBundle\NavBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @group bootable
 */
final class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(NavBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testTheBundleIsBootable(): void
    {
        $kernel = self::bootKernel(['config' => static function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__.'/config/config.yml');
        }]);
        $container = $kernel->getContainer();

        $this->assertTrue($container->has('nav.registry'));
        $this->assertTrue($container->has('nav.entity_manager.default'));
    }
}
