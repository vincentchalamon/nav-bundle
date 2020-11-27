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

namespace NavBundle\Tests\Collection;

use Doctrine\Common\Collections\Collection;
use NavBundle\App\Entity\Intervention;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Collection\ExtraLazyCollection;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ExtraLazyCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testItCountsElementsInTheExtraLazyCollection(): void
    {
        $registryMock = $this->prophesize(RegistryInterface::class);
        $collectionMock = $this->prophesize(Collection::class);
        $ownerMock = new \stdClass();
        $managerMock = $this->prophesize(EntityManagerInterface::class);
        $classMetadataMock = $this->prophesize(ClassMetadataInterface::class);
        $requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);

        $registryMock->getManagerForClass(\stdClass::class)->willReturn($managerMock)->shouldBeCalledOnce();
        $managerMock->getClassMetadata(\stdClass::class)->willReturn($classMetadataMock)->shouldBeCalledOnce();
        $classMetadataMock->getAssociationTargetClass('interventions')->willReturn(Intervention::class)->shouldBeCalledOnce();

        $registryMock->getManagerForClass(Intervention::class)->willReturn($managerMock)->shouldBeCalledOnce();
        $managerMock->createRequestBuilder(Intervention::class)->willReturn($requestBuilderMock)->shouldBeCalledOnce();
        $classMetadataMock->getAssociationMappedByTargetField('interventions')->willReturn('user')->shouldBeCalledOnce();
        $classMetadataMock->getIdentifierValue($ownerMock)->willReturn(1)->shouldBeCalledOnce();
        $requestBuilderMock->andWhere('user', 1)->willReturn($requestBuilderMock)->shouldBeCalledOnce();
        $requestBuilderMock->count()->willReturn(3)->shouldBeCalledOnce();

        $collection = new ExtraLazyCollection($registryMock->reveal(), $collectionMock->reveal(), 'interventions', $ownerMock);
        $this->assertFalse($collection->isInitialized());
        $this->assertEquals(3, $collection->count());
        $this->assertFalse($collection->isInitialized());
    }
}
