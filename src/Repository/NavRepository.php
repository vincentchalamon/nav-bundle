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

namespace NavBundle\Repository;

use NavBundle\Manager\NavManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class NavRepository implements NavRepositoryInterface
{
    protected $manager;
    protected $namespace;

    public function __construct(NavManagerInterface $manager, string $namespace)
    {
        $this->manager = $manager;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function find(string $no)
    {
        return $this->manager->find($this->namespace, $no);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->manager->findAll($this->namespace);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria = [], int $size = 0)
    {
        return $this->manager->findBy($this->namespace, $criteria, $size);
    }
}
