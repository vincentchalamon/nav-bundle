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
use NavBundle\EntityPersister\EntityPersisterInterface;
use NavBundle\EntityRepository\EntityRepository;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Tests\ProphecyTrait;
use NavBundle\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private $repository;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $managerMock;

    /**
     * @var ObjectProphecy|UnitOfWork
     */
    private $unitOfWorkMock;

    /**
     * @var ObjectProphecy|EntityPersisterInterface
     */
    private $entityPersisterMock;

    /**
     * @var ObjectProphecy|RequestBuilderInterface
     */
    private $requestBuilderMock;

    /**
     * @var ObjectProphecy|\Iterator
     */
    private $iteratorMock;

    /**
     * @var ObjectProphecy|\stdClass
     */
    private $objectMock;

    protected function setUp(): void
    {
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->unitOfWorkMock = $this->prophesize(UnitOfWork::class);
        $this->entityPersisterMock = $this->prophesize(EntityPersisterInterface::class);
        $this->requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);
        $this->iteratorMock = $this->prophesize(\Iterator::class);
        $this->objectMock = $this->prophesize(\stdClass::class);

        $this->repository = new EntityRepository($this->managerMock->reveal(), \stdClass::class);
    }

    public function testItFindsAnObjectByItsIdentifier(): void
    {
        $this->managerMock->find(\stdClass::class, 1)->willReturn($this->objectMock->reveal())->shouldBeCalledOnce();

        $this->assertSame($this->objectMock->reveal(), $this->repository->find(1));
    }

    public function testItFindsAllObjects(): void
    {
        $this->managerMock->getUnitOfWork()->willReturn($this->unitOfWorkMock)->shouldBeCalledOnce();
        $this->unitOfWorkMock->getEntityPersister(\stdClass::class)->willReturn($this->entityPersisterMock)->shouldBeCalledOnce();
        $this->entityPersisterMock->loadAll([], null, null)->willReturn($this->iteratorMock->reveal())->shouldBeCalledOnce();

        $this->assertSame($this->iteratorMock->reveal(), $this->repository->findAll());
    }

    public function testItFindsObjectsByCriteria(): void
    {
        $this->managerMock->getUnitOfWork()->willReturn($this->unitOfWorkMock)->shouldBeCalledOnce();
        $this->unitOfWorkMock->getEntityPersister(\stdClass::class)->willReturn($this->entityPersisterMock)->shouldBeCalledOnce();
        $this->entityPersisterMock->loadAll(['foo' => 'bar'], 10, 'bookmarkKey')->willReturn($this->iteratorMock->reveal())->shouldBeCalledOnce();

        $this->assertSame($this->iteratorMock->reveal(), $this->repository->findBy(['foo' => 'bar'], null, 10, 'bookmarkKey'));
    }

    public function testItFindsOneObjectByCriteria(): void
    {
        $this->managerMock->getUnitOfWork()->willReturn($this->unitOfWorkMock)->shouldBeCalledOnce();
        $this->unitOfWorkMock->getEntityPersister(\stdClass::class)->willReturn($this->entityPersisterMock)->shouldBeCalledOnce();
        $this->entityPersisterMock->load(['foo' => 'bar'])->willReturn($this->objectMock->reveal())->shouldBeCalledOnce();

        $this->assertSame($this->objectMock->reveal(), $this->repository->findOneBy(['foo' => 'bar']));
    }

    public function testItGetsTheOriginalClassName(): void
    {
        $this->assertSame(\stdClass::class, $this->repository->getClassName());
    }
}
