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

namespace NavBundle\Tests\Bridge\PagerFanta\Adapter;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavAdapterTest extends TestCase
{
    use ProphecyTrait;

    private $adapter;

    /**
     * @var ObjectProphecy|RequestBuilderInterface
     */
    private $requestBuilderMock;

    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;

    protected function setUp(): void
    {
        $this->requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->adapter = new NavAdapter($this->requestBuilderMock->reveal(), $this->classMetadataMock->reveal());
    }

    public function testItGetsNbResultsFromRequestBuilder(): void
    {
        $this->requestBuilderMock->count()->willReturn(3)->shouldBeCalledOnce();

        $this->assertEquals(3, $this->adapter->getNbResults());
    }

    public function testItGetsASliceOfResults(): void
    {
        /** @var ObjectProphecy|\ArrayIterator $iteratorMock */
        $iteratorMock = $this->prophesize(\ArrayIterator::class);
        /** @var ObjectProphecy|\stdClass $objectMock */
        $objectMock = $this->prophesize(\stdClass::class);

        $this->requestBuilderMock->copy()->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setBookmarkKey('foo')->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->setSize(3)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->requestBuilderMock->getResult()->willReturn($iteratorMock->reveal())->shouldBeCalledOnce();

        $iteratorMock->count()->willReturn(3)->shouldBeCalledOnce();
        $iteratorMock->seek(2)->shouldBeCalledOnce();
        $iteratorMock->current()->willReturn($objectMock)->shouldBeCalledOnce();
        $this->classMetadataMock->getKeyValue($objectMock)->willReturn('azerty')->shouldBeCalledOnce();
        $iteratorMock->rewind()->shouldBeCalledOnce();

        $this->assertNull($this->adapter->getBookmarkKey());
        $this->assertSame($iteratorMock->reveal(), $this->adapter->getSlice('foo', 3));
        $this->assertEquals('azerty', $this->adapter->getBookmarkKey());
    }
}
