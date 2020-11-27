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

namespace NavBundle\Tests\Hydrator;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Hydrator\CountHydrator;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CountHydratorTest extends TestCase
{
    use ProphecyTrait;

    private $hydrator;

    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;

    protected function setUp(): void
    {
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->hydrator = new CountHydrator();
    }

    public function testItReturns0IfDataIsEmpty(): void
    {
        $this->classMetadataMock->getNamespace()->willReturn('FOO')->shouldBeCalled();

        $this->assertEquals(0, $this->hydrator->hydrateAll((object) [], $this->classMetadataMock->reveal()));
        $this->assertEquals(0, $this->hydrator->hydrateAll((object) ['ReadMultiple_Result' => (object) []], $this->classMetadataMock->reveal()));
    }

    public function testItReturnsTheNumberOfElements(): void
    {
        $this->classMetadataMock->getNamespace()->willReturn('FOO')->shouldBeCalled();

        $this->assertEquals(1, $this->hydrator->hydrateAll((object) ['ReadMultiple_Result' => (object) ['FOO' => (object) []]], $this->classMetadataMock->reveal()));
        $this->assertEquals(2, $this->hydrator->hydrateAll((object) ['ReadMultiple_Result' => (object) ['FOO' => [(object) [], (object) []]]], $this->classMetadataMock->reveal()));
    }
}
