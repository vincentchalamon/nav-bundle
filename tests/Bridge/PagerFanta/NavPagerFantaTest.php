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

namespace NavBundle\Tests\Bridge\PagerFanta;

use NavBundle\Bridge\Pagerfanta\Adapter\NavAdapter;
use NavBundle\Bridge\Pagerfanta\NavPagerFanta;
use Pagerfanta\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class NavPagerFantaTest extends TestCase
{
    private $pager;

    /**
     * @var ObjectProphecy|NavAdapter
     */
    private $adapterMock;

    protected function setUp(): void
    {
        $this->adapterMock = $this->prophesize(NavAdapter::class);

        $this->pager = new NavPagerFanta($this->adapterMock->reveal());
    }

    public function testItDoesNotAcceptInvalidAdapter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new NavPagerFanta($this->prophesize(AdapterInterface::class)->reveal());
    }

    public function testItGetsCurrentPageResults(): void
    {
        $data = $this->prophesize(\Traversable::class);
        $this->adapterMock->getSlice(null, 10)->willReturn($data)->shouldBeCalledOnce();

        $this->assertSame($data->reveal(), $this->pager->getCurrentPageResults());
    }

    public function testItChecksIfPagerHasANextPage(): void
    {
        $this->adapterMock->getBookmarkKey()->willReturn(null, 'plop')->shouldBeCalled();

        $this->assertFalse($this->pager->hasNextPage());
        $this->assertTrue($this->pager->hasNextPage());
    }
}
