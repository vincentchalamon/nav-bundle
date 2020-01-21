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

namespace NavBundle\Bridge\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;
use NavBundle\Util\ClassUtils;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DataPersister implements DataPersisterInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $this->getManager($data) instanceof EntityManagerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($data): void
    {
        $em = $this->getManager($data);
        $em->persist($data);
        $em->flush($data);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data): void
    {
        $em = $this->getManager($data);
        $em->remove($data);
        $em->flush($data);
    }

    private function getManager($data): ?EntityManagerInterface
    {
        return $this->registry->getManagerForClass(ClassUtils::getRealClass($data));
    }
}
