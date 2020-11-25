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

namespace NavBundle\Tests\EntityRepository;

use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityRepository\ServiceEntityRepository;
use NavBundle\Exception\EntityManagerNotFoundException;
use NavBundle\RegistryInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ServiceEntityRepositoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItRetrievesManagerForClassFromRegistry(): void
    {
        $registryMock = $this->prophesize(RegistryInterface::class);
        $managerMock = $this->prophesize(EntityManagerInterface::class);

        $registryMock->getManagerForClass(\stdClass::class)->willReturn($managerMock->reveal())->shouldBeCalledOnce();

        new ServiceEntityRepository($registryMock->reveal(), \stdClass::class);
    }

    public function testItThrowsAnExceptionIfTheManagerCannotBeFound(): void
    {
        $registryMock = $this->prophesize(RegistryInterface::class);

        $registryMock->getManagerForClass(\stdClass::class)->willReturn(null)->shouldBeCalledOnce();
        $this->expectException(EntityManagerNotFoundException::class);

        new ServiceEntityRepository($registryMock->reveal(), \stdClass::class);
    }
}
