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

use NavBundle\Serializer\CollectionDenormalizer;
use NavBundle\Serializer\NavDecoder;
use NavBundle\Serializer\ObjectDenormalizer;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    private $denormalizer;

    /**
     * @var ObjectProphecy|DenormalizerInterface
     */
    private $denormalizerMock;

    protected function setUp(): void
    {
        $this->denormalizerMock = $this->prophesize(DenormalizerInterface::class);

        $this->denormalizer = new CollectionDenormalizer();
        $this->denormalizer->setDenormalizer($this->denormalizerMock->reveal());
    }

    public function testItDoesNotSupportInvalidFormat(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization(['ReadMultiple_Result' => []], \stdClass::class, 'invalid'));
    }

    public function testItDoesNotSupportInvalidData(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization('invalid', \stdClass::class, NavDecoder::FORMAT));
        $this->assertFalse($this->denormalizer->supportsDenormalization([], \stdClass::class, NavDecoder::FORMAT));
    }

    public function testItDoesNotSupportInvalidContext(): void
    {
        $this->assertFalse($this->denormalizer->supportsDenormalization([
            'ReadMultiple_Result' => [],
        ], \stdClass::class, NavDecoder::FORMAT));
    }

    public function testItSupportsValidFormatAndDataAndContext(): void
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([
            'ReadMultiple_Result' => [],
        ], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItDenormalizesEmptyData(): void
    {
        $this->assertEquals(new \ArrayIterator(), $this->denormalizer->denormalize([
            'ReadMultiple_Result' => [],
        ], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
        $this->assertEquals(new \ArrayIterator(), $this->denormalizer->denormalize([
            'ReadMultiple_Result' => [
                'FOO' => [],
            ],
        ], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItDenormalizesSingleResultData(): void
    {
        $object = (object) ['key' => 'foo', 'no' => 'bar'];

        $this->denormalizerMock->denormalize([
            'Key' => 'foo',
            'No' => 'bar',
        ], \stdClass::class, NavDecoder::FORMAT, [
            ObjectDenormalizer::NAMESPACE => 'FOO',
        ])->willReturn($object)->shouldBeCalledOnce();
        $this->assertEquals(new \ArrayIterator([$object]), $this->denormalizer->denormalize([
            'ReadMultiple_Result' => [
                'FOO' => [
                    'Key' => 'foo',
                    'No' => 'bar',
                ],
            ],
        ], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }

    public function testItDenormalizesMultipleResultsData(): void
    {
        $object = (object) ['key' => 'foo', 'no' => 'bar'];

        $this->denormalizerMock->denormalize([
            'Key' => 'foo',
            'No' => 'bar',
        ], \stdClass::class, NavDecoder::FORMAT, [
            ObjectDenormalizer::NAMESPACE => 'FOO',
        ])->willReturn($object)->shouldBeCalledTimes(2);
        $this->assertEquals(new \ArrayIterator([$object, $object]), $this->denormalizer->denormalize([
            'ReadMultiple_Result' => [
                'FOO' => [
                    [
                        'Key' => 'foo',
                        'No' => 'bar',
                    ],
                    [
                        'Key' => 'foo',
                        'No' => 'bar',
                    ],
                ],
            ],
        ], \stdClass::class, NavDecoder::FORMAT, [ObjectDenormalizer::NAMESPACE => 'FOO']));
    }
}
