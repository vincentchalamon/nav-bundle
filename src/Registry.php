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

namespace NavBundle;

use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\NavManagerInterface;
use NavBundle\Repository\NavRepositoryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class Registry implements RegistryInterface
{
    /**
     * @var iterable|NavManagerInterface[]
     */
    private $managers;
    private $wsdl;
    private $defaultOptions;

    public function __construct(iterable $managers, string $wsdl, array $defaultOptions)
    {
        $this->managers = $managers;
        $this->wsdl = $wsdl;
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getManager(string $name = 'default'): NavManagerInterface
    {
        if (!isset($this->managers[$name])) {
            throw new ManagerNotFoundException("Manager $name not found.");
        }

        return $this->managers[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getManagerForClass(string $class): NavManagerInterface
    {
        foreach ($this->managers as $manager) {
            if ($manager->hasClass($class)) {
                return $manager;
            }
        }

        throw new ManagerNotFoundException("No manager found for class $class.");
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $class): NavRepositoryInterface
    {
        return $this->getManager($class)->getRepository($class);
    }
}
