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

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\RegistryInterface;

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
        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass($resourceClass);
        $builder = $manager->createRequestBuilder($resourceClass);
        // TODO: Temporary set limit, waiting for PaginationExtension.
        $builder->setSize(10);
        foreach ($this->extensions as $extension) {
            $extension->applyToCollection($builder, $resourceClass, $operationName, $context);
            if ($extension instanceof ResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName, $context)) {
                return $extension->getResult($builder, $resourceClass, $operationName, $context);
            }
        }

        return $builder->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $this->registry->getManagerForClass($resourceClass) instanceof EntityManagerInterface;
    }
}
