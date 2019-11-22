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
    protected $className;

    public function __construct(ManagerInterface $manager, string $className)
    {
        $this->manager = $manager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $no)
    {
        return $this->manager->find($this->className, $no);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->manager->findAll($this->className);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [], int $size = 0)
    {
        return $this->manager->findBy($this->className, $criteria, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria = [])
    {
        return $this->manager->findOneBy($this->className, $criteria);
    }
}
