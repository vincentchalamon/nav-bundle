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

use Doctrine\Persistence\ObjectRepository;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityRepository\EntityRepository;
use NavBundle\EntityRepository\EntityRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class EntityRepositoryFactoryTest extends TestCase
{
    private $factory;

    /**
     * @var ObjectProphecy|ObjectRepository
     */
    private $repositoryMock;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $managerMock;

    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->prophesize(ObjectRepository::class);
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->factory = new EntityRepositoryFactory([$this->repositoryMock->reveal()]);
    }

    public function testItGetsRepositoryFromConstructorArguments(): void
    {
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->getEntityRepositoryClass()->willReturn(\get_class($this->repositoryMock->reveal()))->shouldBeCalledOnce();
        $this->classMetadataMock->getName()->shouldNotBeCalled();

        $this->assertSame($this->repositoryMock->reveal(), $this->factory->getRepository($this->managerMock->reveal(), \stdClass::class));
    }

    public function testItCreatesRepository(): void
    {
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalled();
        $this->classMetadataMock->getEntityRepositoryClass()->willReturn(EntityRepository::class)->shouldBeCalled();
        $this->classMetadataMock->getName()->willReturn(\stdClass::class)->shouldBeCalled();

        $repository = $this->factory->getRepository($this->managerMock->reveal(), \stdClass::class);

        $this->assertInstanceOf(EntityRepository::class, $repository);
        $this->assertNotSame($this->repositoryMock->reveal(), $repository);
        $this->assertSame($repository, $this->factory->getRepository($this->managerMock->reveal(), \stdClass::class));
    }
}
