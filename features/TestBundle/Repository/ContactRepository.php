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

use NavBundle\E2e\TestBundle\Entity\Contact;
use NavBundle\EntityRepository\ServiceEntityRepository;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): iterable
    {
        return parent::findBy($criteria + ['type' => 'Person'], $orderBy, $limit, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): iterable
    {
        return $this->findBy(['type' => 'Person']);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        return parent::findOneBy($criteria + ['type' => 'Person'], $orderBy);
    }
}
