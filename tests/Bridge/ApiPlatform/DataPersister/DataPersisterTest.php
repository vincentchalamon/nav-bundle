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

namespace NavBundle\Tests\Bridge\ApiPlatform\DataPersister;

use NavBundle\Bridge\ApiPlatform\DataPersister\DataPersister;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DataPersisterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $registryMock;

    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $emMock;
    private $dataPersister;

    protected function setUp(): void
    {
        $this->registryMock = $this->prophesize(RegistryInterface::class);
        $this->emMock = $this->prophesize(EntityManagerInterface::class);

        $this->dataPersister = new DataPersister($this->registryMock->reveal());
    }

    public function testItSupportsValidData(): void
    {
        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->emMock)->shouldBeCalledOnce();

        $this->assertTrue($this->dataPersister->supports(new \stdClass()));
    }

    public function testItPersistsAData(): void
    {
        $data = new \stdClass();

        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->emMock)->shouldBeCalledOnce();
        $this->emMock->persist($data)->shouldBeCalledOnce();
        $this->emMock->flush($data)->shouldBeCalledOnce();

        $this->dataPersister->persist($data);
    }

    public function testItRemovesAData(): void
    {
        $data = new \stdClass();

        $this->registryMock->getManagerForClass(\stdClass::class)->willReturn($this->emMock)->shouldBeCalledOnce();
        $this->emMock->remove($data)->shouldBeCalledOnce();
        $this->emMock->flush($data)->shouldBeCalledOnce();

        $this->dataPersister->remove($data);
    }
}
