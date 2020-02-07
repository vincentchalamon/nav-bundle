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
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavDecoderTest extends TestCase
{
    private $decoder;

    protected function setUp(): void
    {
        $this->decoder = new NavDecoder();
    }

    public function testItDoesNotSupportInvalidFormat(): void
    {
        $this->assertFalse($this->decoder->supportsDecoding('invalid'));
    }

    public function testItSupportsValidFormat(): void
    {
        $this->assertTrue($this->decoder->supportsDecoding(NavDecoder::FORMAT));
    }

    public function testItDecodesObjectToArray(): void
    {
        $this->assertEquals([
            'foo' => [
                'bar' => [
                    'lorem' => 'ipsum',
                ],
            ],
        ], $this->decoder->decode((object) [
            'foo' => [
                'bar' => (object) [
                    'lorem' => 'ipsum',
                ],
            ],
        ], NavDecoder::FORMAT));
    }
}
