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

namespace NavBundle\Tests\Bridge\ApiPlatform\DataProvider;

use NavBundle\Bridge\ApiPlatform\DataProvider\ItemDataProvider;
use NavBundle\Bridge\ApiPlatform\DataProvider\ItemExtensionInterface;
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
final class ItemDataProviderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $registryMock;
    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $emMock;
    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;
    /**
     * @var ObjectProphecy|RequestBuilderInterface
     */
    private $requestBuilderMock;
    /**
     * @var ObjectProphecy|\stdClass
     */
    private $resultMock;
    /**
     * @var ObjectProphecy|ItemExtensionInterface
     */
    private $extensionMock;
    private $dataProvider;

    protected function setUp(): void
    {
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->emMock = $this->prophesize(EntityManagerInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);
        $this->requestBuilderMock = $this->prophesize(RequestBuilderInterface::class);
        $this->resultMock = $this->prophesize(\stdClass::class);
        $this->extensionMock = $this->prophesize(ItemExtensionInterface::class);

        $this->dataProvider = new ItemDataProvider($this->registryMock->reveal(), [$this->extensionMock->reveal()]);
    }

    public function testItSupportsResourceClass(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->emMock)->shouldBeCalledOnce();

        $this->assertTrue($this->dataProvider->supports(\stdClass::class));
    }

    public function testItGetsCollection(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->emMock)->shouldBeCalledOnce();
        $this->emMock->createRequestBuilder(\stdClass::class)->willReturn($this->requestBuilderMock)->shouldBeCalledOnce();
        $this->emMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->getIdentifier()->willReturn('no')->shouldBeCalledOnce();
        $this->requestBuilderMock->where('no', '12345')->shouldBeCalledOnce();

        $this->extensionMock
            ->applyToItem($this->requestBuilderMock, \stdClass::class, '12345', null, [])
            ->shouldBeCalledOnce();
        $this->requestBuilderMock->getOneOrNullResult()->willReturn($this->resultMock)->shouldBeCalledOnce();

        $this->assertSame($this->resultMock->reveal(), $this->dataProvider->getItem(\stdClass::class, '12345'));
    }
}
