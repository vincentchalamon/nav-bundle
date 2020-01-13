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

namespace Backup\NavBundle\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Backup\NavBundle\Exception\ManagerNotFoundException;
use Backup\NavBundle\Manager\ManagerInterface;
use Backup\NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $registry;
    private $extensions;

    /**
     * @param CollectionExtensionInterface[] $extensions
     */
    public function __construct(RegistryInterface $registry, iterable $extensions)
    {
        $this->registry = $registry;
        $this->extensions = $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $manager = $this->registry->getManagerForClass($resourceClass);
        $criteria = [];
        foreach ($this->extensions as $extension) {
            $extension->applyToCollection($criteria, $resourceClass, $operationName, $context);
        }

        return $manager->getRepository($resourceClass)->findBy($criteria);
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
