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

namespace NavBundle\Tests\Util;

use NavBundle\Util\UrlUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class UrlUtilsTest extends TestCase
{
    public function testItBuildsAnUrlFromParseUrl(): void
    {
        $this->assertEquals(
            'https://user:pass@www.example.com:8000/foo/bar?lorem=ipsum#hash',
            UrlUtils::build(parse_url('https://user:pass@www.example.com:8000/foo/bar?lorem=ipsum#hash'))
        );
    }
}
