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

namespace NavBundle\Tests\Bridge\FrameworkExtraBundle\Request;

use Doctrine\Persistence\ObjectRepository;
use NavBundle\Bridge\FrameworkExtraBundle\Request\ParamConverter;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as Configuration;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ParamConverterTest extends TestCase
{
    private $paramConverter;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $registryMock;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $managerMock;

    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;

    /**
     * @var ObjectProphecy|ObjectRepository
     */
    private $repositoryMock;

    /**
     * @var ObjectProphecy|Request
     */
    private $requestMock;

    /**
     * @var ObjectProphecy|ParameterBag
     */
    private $attributesMock;

    /**
     * @var ObjectProphecy|Configuration
     */
    private $configurationMock;

    /**
     * @var ObjectProphecy|\stdClass
     */
    private $objectMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);
        $this->repositoryMock = $this->prophesize(ObjectRepository::class);
        $this->requestMock = $this->prophesize(Request::class);
        $this->requestMock->attributes = $this->attributesMock = $this->prophesize(ParameterBag::class);
        $this->configurationMock = $this->prophesize(Configuration::class);
        $this->objectMock = $this->prophesize(\stdClass::class);

        $this->paramConverter = new ParamConverter($this->registryMock->reveal());
    }

    public function testItDoesNotSupportInvalidClass(): void
    {
        $this->configurationMock->getClass()->willReturn(null, 'invalid', \stdClass::class)->shouldBeCalled();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn(null)->shouldBeCalledOnce();

        $this->assertFalse($this->paramConverter->supports($this->configurationMock->reveal()));
        $this->assertFalse($this->paramConverter->supports($this->configurationMock->reveal()));
        $this->assertFalse($this->paramConverter->supports($this->configurationMock->reveal()));
    }

    public function testItSupportsValidClass(): void
    {
        $this->configurationMock->getClass()->willReturn(\stdClass::class)->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();

        $this->assertTrue($this->paramConverter->supports($this->configurationMock->reveal()));
    }

    public function testItThrowsAnExceptionIfObjectIsNotFound(): void
    {
        $this->configurationMock->getClass()->willReturn(\stdClass::class)->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->managerMock->getRepository(\stdClass::class)->willReturn($this->repositoryMock)->shouldBeCalledOnce();
        $this->attributesMock->all()->willReturn(['no' => '12345'])->shouldBeCalledOnce();
        $this->classMetadataMock->getFieldNames()->willReturn(['no', 'name', 'description'])->shouldBeCalledOnce();
        $this->repositoryMock->findOneBy(['no' => '12345'])->willReturn(null)->shouldBeCalledOnce();

        $this->configurationMock->getName()->shouldNotBeCalled();
        $this->expectException(NotFoundHttpException::class);

        $this->paramConverter->apply($this->requestMock->reveal(), $this->configurationMock->reveal());
    }

    public function testItSetsObjectAsRequestAttribute(): void
    {
        $this->configurationMock->getClass()->willReturn(\stdClass::class)->shouldBeCalledOnce();
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->managerMock->getRepository(\stdClass::class)->willReturn($this->repositoryMock)->shouldBeCalledOnce();
        $this->attributesMock->all()->willReturn(['no' => '12345'])->shouldBeCalledOnce();
        $this->classMetadataMock->getFieldNames()->willReturn(['no', 'name', 'description'])->shouldBeCalledOnce();
        $this->repositoryMock->findOneBy(['no' => '12345'])->willReturn($this->objectMock)->shouldBeCalledOnce();

        $this->configurationMock->getName()->willReturn('object')->shouldBeCalledOnce();
        $this->attributesMock->set('object', $this->objectMock)->shouldBeCalledOnce();

        $this->assertTrue($this->paramConverter->apply($this->requestMock->reveal(), $this->configurationMock->reveal()));
    }
}
