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

namespace NavBundle\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $registry;
    private $extensions;

    /**
     * @param ItemExtensionInterface[] $extensions
     */
    public function __construct(RegistryInterface $registry, iterable $extensions)
    {
        $this->registry = $registry;
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $resourceClass, $no, string $operationName = null, array $context = []): ?object
    {
        $manager = $this->registry->getManagerForClass($resourceClass);
        $criteria = [$manager->getClassMetadata($resourceClass)->getNo() => $no];
        foreach ($this->extensions as $extension) {
            $extension->applyToItem($criteria, $resourceClass, $no, $operationName, $context);
        }

        return $manager->getRepository($resourceClass)->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        try {
            return $this->registry->getManagerForClass($resourceClass) instanceof ManagerInterface;
        } catch (ManagerNotFoundException $exception) {
            return false;
        }
    }
}
