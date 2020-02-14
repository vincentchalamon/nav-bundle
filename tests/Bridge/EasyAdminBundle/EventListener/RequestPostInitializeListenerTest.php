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

namespace NavBundle\Tests\Bridge\EasyAdminBundle\EventListener;

use Doctrine\Persistence\ObjectRepository;
use EasyCorp\Bundle\EasyAdminBundle\EventListener\RequestPostInitializeListener as EasyAdminRequestPostInitializeListener;
use NavBundle\Bridge\EasyAdminBundle\EventListener\RequestPostInitializeListener;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class RequestPostInitializeListenerTest extends TestCase
{
    private $listener;

    /**
     * @var ObjectProphecy|EasyAdminRequestPostInitializeListener
     */
    private $decoratedMock;

    /**
     * @var ObjectProphecy|RequestStack
     */
    private $requestStackMock;

    /**
     * @var ObjectProphecy|Request
     */
    private $requestMock;

    /**
     * @var ObjectProphecy|ParameterBag
     */
    private $queryMock;

    /**
     * @var ObjectProphecy|ParameterBag
     */
    private $attributesMock;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $registryMock;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $managerMock;

    /**
     * @var ObjectProphecy|ObjectRepository
     */
    private $repositoryMock;

    /**
     * @var ObjectProphecy|GenericEvent
     */
    private $eventMock;

    /**
     * @var ObjectProphecy|\stdClass
     */
    private $objectMock;

    protected function setUp(): void
    {
        $this->decoratedMock = $this->prophesize(EasyAdminRequestPostInitializeListener::class);
        $this->requestStackMock = $this->prophesize(RequestStack::class);
        $this->requestMock = $this->prophesize(Request::class);
        $this->requestMock->query = $this->queryMock = $this->prophesize(ParameterBag::class);
        $this->requestMock->attributes = $this->attributesMock = $this->prophesize(ParameterBag::class);
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->repositoryMock = $this->prophesize(ObjectRepository::class);
        $this->eventMock = $this->prophesize(GenericEvent::class);
        $this->objectMock = $this->prophesize(\stdClass::class);

        $this->listener = new RequestPostInitializeListener(
            $this->decoratedMock->reveal(),
            $this->requestStackMock->reveal(),
            $this->registryMock->reveal()
        );
    }

    public function testItCallsDecoratedServiceOnInvalidClass(): void
    {
        $this->eventMock->getArgument('entity')->willReturn(['class' => \stdClass::class])->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn(null)->shouldBeCalledOnce();
        $this->decoratedMock->initializeRequest($this->eventMock)->shouldBeCalledOnce();
        $this->requestStackMock->getCurrentRequest()->shouldNotBeCalled();

        $this->listener->initializeRequest($this->eventMock->reveal());
    }

    public function testItCannotInitializeInvalidRequest(): void
    {
        $this->eventMock->getArgument('entity')->willReturn(['class' => \stdClass::class])->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->decoratedMock->initializeRequest(Argument::any())->shouldNotBeCalled();
        $this->requestStackMock->getCurrentRequest()->willReturn(null)->shouldBeCalledOnce();

        $this->queryMock->get(Argument::any())->shouldNotBeCalled();

        $this->listener->initializeRequest($this->eventMock->reveal());
    }

    public function testItInitializesRequest(): void
    {
        $this->eventMock->getArgument('entity')->willReturn(['class' => \stdClass::class])->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->decoratedMock->initializeRequest(Argument::any())->shouldNotBeCalled();
        $this->requestStackMock->getCurrentRequest()->willReturn($this->requestMock)->shouldBeCalledOnce();
        $this->queryMock->get('id')->willReturn('azerty')->shouldBeCalledOnce();
        $this->managerMock->getRepository(\stdClass::class)->willReturn($this->repositoryMock)->shouldBeCalledOnce();
        $this->repositoryMock->find('azerty')->willReturn($this->objectMock)->shouldBeCalledOnce();
        $this->queryMock->get('action', 'list')->willReturn('list')->shouldBeCalledOnce();
        $this->attributesMock->set('easyadmin', [
            'entity' => ['class' => \stdClass::class],
            'view' => 'list',
            'item' => $this->objectMock,
        ])->shouldBeCalledOnce();

        $this->listener->initializeRequest($this->eventMock->reveal());
    }
}
