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
use NavBundle\EntityManager\EntityManagerInterface;
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
    public function getItem(string $resourceClass, $identifier, string $operationName = null, array $context = []): ?object
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass($resourceClass);
        $builder = $manager->createRequestBuilder($resourceClass);
        $builder->where($manager->getClassMetadata($resourceClass)->getIdentifier(), $identifier);
        foreach ($this->extensions as $extension) {
            $extension->applyToItem($builder, $resourceClass, $identifier, $operationName, $context);
        }

        return $builder->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $this->registry->getManagerForClass($resourceClass) instanceof EntityManagerInterface;
    }
}
