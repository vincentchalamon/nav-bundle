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

namespace NavBundle\Tests\Serializer\NameConverter;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\RegistryInterface;
use NavBundle\Serializer\NameConverter\CamelCaseToNavNameConverter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CamelCaseToNavNameConverterTest extends TestCase
{
    private $nameConverter;

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

    protected function setUp(): void
    {
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->managerMock = $this->prophesize(EntityManagerInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->nameConverter = new CamelCaseToNavNameConverter($this->registryMock->reveal());
    }

    public function testItNormalizesRegularFieldName(): void
    {
        $this->assertEquals('Column_Name', $this->nameConverter->normalize('columnName'));
    }

    public function testItNormalizesFieldNameFromClassMetadataField(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->hasField('columnName')->willReturn(true)->shouldBeCalledOnce();
        $this->classMetadataMock->hasAssociation(Argument::any())->shouldNotBeCalled();
        $this->classMetadataMock->isSingleValuedAssociation(Argument::any())->shouldNotBeCalled();
        $this->classMetadataMock->getFieldColumnName('columnName')->willReturn('Column_Name')->shouldBeCalledOnce();

        $this->assertEquals('Column_Name', $this->nameConverter->normalize('columnName', \stdClass::class));
    }

    public function testItNormalizesFieldNameFromClassMetadataSingleValuedAnnotation(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->hasField('columnName')->willReturn(false)->shouldBeCalledOnce();
        $this->classMetadataMock->hasAssociation('columnName')->willReturn(true)->shouldBeCalledOnce();
        $this->classMetadataMock->isSingleValuedAssociation('columnName')->willReturn(true)->shouldBeCalledOnce();
        $this->classMetadataMock->getFieldColumnName(Argument::any())->shouldNotBeCalled();
        $this->classMetadataMock->getSingleValuedAssociationColumnName('columnName')->willReturn('Column_Name')->shouldBeCalledOnce();

        $this->assertEquals('Column_Name', $this->nameConverter->normalize('columnName', \stdClass::class));
    }

    public function testItNormalizesFieldNameFromClassMetadataMultipleValuedAnnotation(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->hasField('columnName')->willReturn(false)->shouldBeCalledOnce();
        $this->classMetadataMock->hasAssociation('columnName')->willReturn(true)->shouldBeCalledOnce();
        $this->classMetadataMock->isSingleValuedAssociation('columnName')->willReturn(false)->shouldBeCalledOnce();
        $this->classMetadataMock->getFieldColumnName(Argument::any())->shouldNotBeCalled();
        $this->classMetadataMock->getSingleValuedAssociationColumnName(Argument::any())->shouldNotBeCalled();

        $this->assertEquals('Column_Name', $this->nameConverter->normalize('columnName', \stdClass::class));
    }

    public function testItDenormalizesRegularColumnName(): void
    {
        $this->assertEquals('columnName', $this->nameConverter->denormalize('Column_Name'));
    }

    public function testItDenormalizesColumnNameFromClassMetadataField(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->retrieveField('Column_Name')->willReturn('columnName')->shouldBeCalledOnce();
        $this->classMetadataMock->retrieveSingleValuedAssociation('Column_Name')->shouldNotBeCalled();

        $this->assertEquals('columnName', $this->nameConverter->denormalize('Column_Name', \stdClass::class));
    }

    public function testItDenormalizesColumnNameFromClassMetadataAssociation(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->managerMock)->shouldBeCalledOnce();
        $this->managerMock->getClassMetadata(\stdClass::class)->willReturn($this->classMetadataMock)->shouldBeCalledOnce();
        $this->classMetadataMock->retrieveField('Column_Name')->willThrow(FieldNotFoundException::class)->shouldBeCalledOnce();
        $this->classMetadataMock->retrieveSingleValuedAssociation('Column_Name')->willReturn('columnName')->shouldBeCalledOnce();

        $this->assertEquals('columnName', $this->nameConverter->denormalize('Column_Name', \stdClass::class));
    }
}
