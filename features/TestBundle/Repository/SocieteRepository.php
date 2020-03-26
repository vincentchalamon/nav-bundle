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

namespace NavBundle\E2e\TestBundle\Repository;

use NavBundle\E2e\TestBundle\Entity\Societe;
use NavBundle\EntityRepository\ServiceEntityRepository;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class SocieteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Societe::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): \Traversable
    {
        return parent::findBy($criteria + ['type' => 'Company', 'noTiers' => 'ACTIONS'], $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): \Traversable
    {
        return $this->findBy(['type' => 'Company', 'noTiers' => 'ACTIONS']);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        return parent::findOneBy($criteria + ['type' => 'Company', 'noTiers' => 'ACTIONS'], $orderBy);
    }
}
