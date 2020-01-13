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

namespace NavBundle\EntityRepository;

use Doctrine\Persistence\ObjectRepository;
use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EntityRepositoryFactoryInterface
{
    /**
     * Gets the repository for an entity class.
     *
     * @param EntityManagerInterface $entityManager the manager of the entity
     * @param string                 $className     the name of the entity
     *
     * @return ObjectRepository
     */
    public function getRepository(EntityManagerInterface $entityManager, string $className);
}
