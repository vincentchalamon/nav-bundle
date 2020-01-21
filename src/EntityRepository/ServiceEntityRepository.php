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

use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Exception\EntityManagerNotFoundException;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class ServiceEntityRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @param string $className The class name of the entity this repository manages
     *
     * @throws EntityManagerNotFoundException
     */
    public function __construct(RegistryInterface $registry, $className)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass($className);

        if (null === $manager) {
            throw new EntityManagerNotFoundException("Could not find the entity manager for class '$className'. Check your NAV configuration to make sure it is configured to load this entity’s metadata.");
        }

        parent::__construct($manager, $className);
    }
}
