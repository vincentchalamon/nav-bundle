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

namespace NavBundle\Tests\Bridge\EasyAdminBundle\Search;

use NavBundle\Bridge\EasyAdminBundle\Search\Paginator;
use NavBundle\Bridge\Pagerfanta\NavPagerFanta;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PaginatorTest extends TestCase
{
    use ProphecyTrait;

    public function testItCreatesANavPaginator(): void
    {
        /** @var ObjectProphecy|RegistryInterface $registryMock */
        $registryMock = $this->prophesize(RegistryInterface::class);
        /** @var ObjectProphecy|RequestBuilderInterface $requestBuilderMock */
        $requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);
        /** @var ObjectProphecy|EntityManagerInterface $managerMock */
        $managerMock = $this->prophesize(EntityManagerInterface::class);
        /** @var ObjectProphecy|ClassMetadataInterface $classMetadataMock */
        $classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $requestBuilderMock->getClassName()->willReturn(\stdClass::class)->shouldBeCalledOnce();
        $registryMock->getManagerForClass(\stdClass::class)->willReturn($managerMock)->shouldBeCalledOnce();
        $managerMock->getClassMetadata(\stdClass::class)->willReturn($classMetadataMock)->shouldBeCalledOnce();

        $paginator = new Paginator($registryMock->reveal());
        /** @var NavPagerFanta $result */
        $result = $paginator->createNavPaginator($requestBuilderMock->reveal(), 'azerty', 10);
        $this->assertInstanceOf(NavPagerFanta::class, $result);
        $this->assertEquals(10, $result->getMaxPerPage());
    }
}
