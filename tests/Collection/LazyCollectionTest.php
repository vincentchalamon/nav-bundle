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
use Doctrine\Persistence\ObjectRepository;
use NavBundle\App\Entity\Intervention;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Collection\LazyCollection;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class LazyCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testItInitializesTheLazyCollection(): void
    {
        $registryMock = $this->prophesize(RegistryInterface::class);
        $collectionMock = $this->prophesize(Collection::class);
        $ownerMock = new \stdClass();
        $managerMock = $this->prophesize(EntityManagerInterface::class);
        $classMetadataMock = $this->prophesize(ClassMetadataInterface::class);
        $repositoryMock = $this->prophesize(ObjectRepository::class);

        $collectionMock->count()->willReturn(0)->shouldBeCalledOnce();
        $collectionMock->toArray()->willReturn([])->shouldBeCalledOnce();
        $collectionMock->clear()->shouldBeCalledOnce();

        $registryMock->getManagerForClass(\stdClass::class)->willReturn($managerMock)->shouldBeCalledOnce();
        $managerMock->getClassMetadata(\stdClass::class)->willReturn($classMetadataMock)->shouldBeCalledOnce();
        $classMetadataMock->getAssociationTargetClass('interventions')->willReturn(Intervention::class)->shouldBeCalledOnce();

        $registryMock->getManagerForClass(Intervention::class)->willReturn($managerMock)->shouldBeCalledOnce();
        $managerMock->getRepository(Intervention::class)->willReturn($repositoryMock)->shouldBeCalledOnce();
        $classMetadataMock->getAssociationMappedByTargetField('interventions')->willReturn('user')->shouldBeCalledOnce();
        $classMetadataMock->getIdentifierValue($ownerMock)->willReturn(1)->shouldBeCalledOnce();
        $repositoryMock->findBy(['user' => 1])->willReturn([new \stdClass()])->shouldBeCalledOnce();

        $collectionMock->add(Argument::type(\stdClass::class), Argument::type('string'))->shouldBeCalledOnce();

        $collection = new LazyCollection($registryMock->reveal(), $collectionMock->reveal(), 'interventions', $ownerMock);
        $this->assertFalse($collection->isInitialized());
        $collection->count();
        $this->assertTrue($collection->isInitialized());
    }
}
