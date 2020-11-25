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

namespace NavBundle\Tests\EntityPersister;

use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityPersister\EntityPersister;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityPersisterTest extends TestCase
{
    use ProphecyTrait;

    private $entityPersister;
    private $iterator;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $managerMock;

    /**
     * @var ObjectProphecy|RequestBuilderInterface
     */
    private $requestBuilderMock;

    protected function setUp(): void
    {
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);
        $this->iterator = new \ArrayIterator([new \stdClass()]);

        $this->entityPersister = new EntityPersister($this->managerMock->reveal(), \stdClass::class);
    }

    public function testItLoadsAllResultsFromCriteria(): void
    {
        $this->managerMock->createRequestBuilder(\stdClass::class)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->andWhere('foo', 'bar')->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setSize(10)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setBookmarkKey('bookmarkKey')->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->getResult()->willReturn($this->iterator)->shouldBeCalledOnce();

        $this->assertSame($this->iterator, $this->entityPersister->loadAll(['foo' => 'bar'], 10, 'bookmarkKey'));
    }

    public function testItLoadsASingleResultFromCriteria(): void
    {
        $this->managerMock->createRequestBuilder(\stdClass::class)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->andWhere('foo', 'bar')->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setSize(1)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setBookmarkKey(Argument::any())->shouldNotBeCalled();
        $this->requestBuilderMock->getResult()->willReturn($this->iterator)->shouldBeCalledOnce();

        $this->assertSame($this->iterator->current(), $this->entityPersister->load(['foo' => 'bar']));
    }

    public function testItLoadsNullFromCriteria(): void
    {
        $this->managerMock->createRequestBuilder(\stdClass::class)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->andWhere('foo', 'bar')->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setSize(1)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setBookmarkKey(Argument::any())->shouldNotBeCalled();
        $this->requestBuilderMock->getResult()->willReturn(new \ArrayIterator())->shouldBeCalledOnce();

        $this->assertNull($this->entityPersister->load(['foo' => 'bar']));
    }
}
