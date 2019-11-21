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
    protected $client;

    public function __construct(ManagerInterface $manager, \SoapClient $client)
    {
        $this->manager = $manager;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $no)
    {
        return $this->manager->find($this->client, $no);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->manager->findAll($this->client);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [], int $size = 0)
    {
        return $this->manager->findBy($this->client, $criteria, $size);
    }
}
