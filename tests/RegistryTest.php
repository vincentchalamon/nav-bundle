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

use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Registry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RegistryTest extends TestCase
{
    private $registry;

    /** @var ObjectProphecy|ContainerInterface */
    private $containerMock;

    /** @var ObjectProphecy|ConnectionInterface */
    private $connectionMock;

    /** @var ObjectProphecy|LazyLoadingValueHolderFactory */
    private $holderFactoryMock;

    /** @var ObjectProphecy|EntityManagerInterface */
    private $managerMock;

    /** @var ObjectProphecy|ClassMetadataFactory */
    private $metadataFactoryMock;

    /** @var ObjectProphecy|ClassMetadataInterface */
    private $classMetadataMock;

    protected function setUp(): void
    {
        $this->containerMock = $this->prophesize(ContainerInterface::class);
        $this->connectionMock = $this->prophesize(ConnectionInterface::class);
        $this->holderFactoryMock = $this->prophesize(LazyLoadingValueHolderFactory::class);
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->metadataFactoryMock = $this->prophesize(ClassMetadataFactory::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->registry = new Registry($this->containerMock->reveal(), [
            'foo' => 'nav.manager.foo',
            'default' => 'nav.manager.default',
        ], 'default', __DIR__);
    }

    public function testItGetsNamespaceFromAlias(): void
    {
        $this->containerMock->get('nav.manager.foo')->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->containerMock->get('nav.manager.default')->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getEntityNamespace('@App\Foo')->willThrow(\InvalidArgumentException::class)->shouldBeCalledOnce();
        $this->managerMock->getEntityNamespace('@App\Foo')->willReturn('App\Entity\Foo')->shouldBeCalledOnce();

        $this->assertEquals('App\Entity\Foo', $this->registry->getAliasNamespace('@App\Foo'));
    }

    public function testItGetsConnectionsFromManagers(): void
    {
        $this->containerMock->get('nav.manager.foo')->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->containerMock->get('nav.manager.default')->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getMetadataFactory()->willReturn($this->metadataFactoryMock)->shouldBeCalledTimes(2);
        $this->metadataFactoryMock->getAllMetadata()->willReturn([$this->classMetadataMock])->shouldBeCalledTimes(2);
        $this->classMetadataMock->getName()->willReturn('App\Entity\Foo', 'App\Entity\Bar')->shouldBeCalledTimes(2);
        $this->managerMock->getConnection('App\Entity\Foo')->willReturn($this->connectionMock)->shouldBeCalledOnce();
        $this->managerMock->getConnection('App\Entity\Bar')->willReturn($this->connectionMock)->shouldBeCalledOnce();

        $this->assertSame([
            'App\Entity\Foo' => $this->connectionMock->reveal(),
            'App\Entity\Bar' => $this->connectionMock->reveal(),
        ], $this->registry->getConnections());
    }

    public function testItWarmsUpConnectionsAndProxies(): void
    {
        $this->containerMock->get('nav.manager.foo')->willReturn($this->managerMock)->shouldBeCalledTimes(2);
        $this->containerMock->get('nav.manager.default')->willReturn($this->managerMock)->shouldBeCalledTimes(2);
        $this->managerMock->getMetadataFactory()->willReturn($this->metadataFactoryMock)->shouldBeCalledTimes(4);
        $this->metadataFactoryMock->getAllMetadata()->willReturn([$this->classMetadataMock])->shouldBeCalledTimes(4);
        $this->classMetadataMock->getName()->willReturn('App\Entity\Foo', 'App\Entity\Bar', 'App\Entity\Foo', 'App\Entity\Bar')->shouldBeCalledTimes(4);
        $warmableMock = $this->prophesize(WarmableInterface::class);
        $this->managerMock->getConnection('App\Entity\Foo')->willReturn($warmableMock->reveal())->shouldBeCalledOnce();
        $this->managerMock->getConnection('App\Entity\Bar')->willReturn($warmableMock->reveal())->shouldBeCalledOnce();
        $warmableMock->warmUp('/tmp')->shouldBeCalledTimes(2);

        $this->containerMock->get('nav.proxy_manager.lazy_loading_value_holder_factory')->willReturn($this->holderFactoryMock)->shouldBeCalledOnce();
        $this->holderFactoryMock
            ->createProxy('App\Entity\Foo', Argument::any())
            ->willReturn($this->prophesize(VirtualProxyInterface::class)->reveal())
            ->shouldBeCalledOnce();
        $this->holderFactoryMock
            ->createProxy('App\Entity\Bar', Argument::any())
            ->willReturn($this->prophesize(VirtualProxyInterface::class)->reveal())
            ->shouldBeCalledOnce();

        $this->registry->warmUp('/tmp');
    }
}
