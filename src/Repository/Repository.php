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

use NavBundle\Manager\ManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Repository implements RepositoryInterface
{
    protected $manager;
    protected $class;

    public function __construct(ManagerInterface $manager, string $class)
    {
        $this->manager = $manager;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $no)
    {
        return $this->manager->find($this->class, $no);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->manager->findAll($this->class);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [], int $size = 0)
    {
        return $this->manager->findBy($this->class, $criteria, $size);
    }
}
