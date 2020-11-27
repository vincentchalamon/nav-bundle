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
use NavBundle\Hydrator\ObjectHydrator;
use NavBundle\Serializer\EntityNormalizer;
use NavBundle\Serializer\NavDecoder;
use NavBundle\Serializer\ObjectDenormalizer;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectHydratorTest extends TestCase
{
    use ProphecyTrait;

    private $hydrator;

    /**
     * @var ObjectProphecy|SerializerInterface
     */
    private $serializerMock;

    /**
     * @var ObjectProphecy|ClassMetadataInterface
     */
    private $classMetadataMock;

    protected function setUp(): void
    {
        $this->serializerMock = $this->prophesize(SerializerInterface::class);
        $this->classMetadataMock = $this->prophesize(ClassMetadataInterface::class);

        $this->hydrator = new ObjectHydrator($this->serializerMock->reveal());
    }

    public function testItDeserializesResponseToObjects(): void
    {
        $this->classMetadataMock->getName()->willReturn('App\Entity\Foo')->shouldBeCalledOnce();
        $this->classMetadataMock->getNamespace()->willReturn('FOO')->shouldBeCalledOnce();
        $this->serializerMock->deserialize(serialize((object) []), 'App\Entity\Foo', NavDecoder::FORMAT, [
            ObjectDenormalizer::NAMESPACE => 'FOO',
            EntityNormalizer::ENABLE_MAX_DEPTH => true,
        ])->willReturn(new \stdClass())->shouldBeCalledOnce();

        $this->assertEquals(new \stdClass(), $this->hydrator->hydrateAll((object) [], $this->classMetadataMock->reveal()));
    }
}
