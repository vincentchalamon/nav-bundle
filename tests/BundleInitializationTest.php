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
use Nyholm\BundleTest\BaseBundleTestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass(): string
    {
        return NavBundle::class;
    }

    public function testTheBundleIsBootable(): void
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config/config.yml');
        $this->bootKernel();

        $container = $this->getContainer();

        $this->assertTrue($container->has('nav.registry'));
        $this->assertTrue($container->has('nav.entity_manager.default'));
    }
}
