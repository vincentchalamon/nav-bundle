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

namespace NavBundle\Tests\Serializer;

use NavBundle\Serializer\NavDecoder;
use NavBundle\Serializer\ObjectDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ObjectDenormalizerTest extends TestCase
{
    private $denormalizer;

    /**
     * @var ObjectProphecy|DenormalizerInterface
     */
    private $denormalizerMock;

    protected function setUp(): void
    {
        $this->denormalizerMock = $this->prophesize(DenormalizerInterface::class);

        $this->denormalizer = new ObjectDenormalizer();
        $this->denormalizer->setDenormalizer($this->denormalizerMock->reveal());
    }

    public function testItDoesNotSupportInvalidFormat(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization(['FOO' => []], \stdClass::class, 'invalid', [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItDoesNotSupportInvalidData(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization('invalid', \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
        $this->assertFalse($this->denormalizer->supportsDenormalization([], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
        $this->assertFalse($this->denormalizer->supportsDenormalization(['FOO' => []], \stdClass::class, NavDecoder::FORMAT));
        $this->assertFalse($this->denormalizer->supportsDenormalization([], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItSupportsValidFormatAndData(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization(['FOO' => []], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItDenormalizesData(): void
    {
        $this->denormalizerMock->denormalize(['foo' => 'bar'], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO'])->willReturn(new \stdClass())->shouldBeCalledOnce();

        $this->assertEquals(new \stdClass(), $this->denormalizer->denormalize(['FOO' => ['foo' => 'bar']], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }
}
