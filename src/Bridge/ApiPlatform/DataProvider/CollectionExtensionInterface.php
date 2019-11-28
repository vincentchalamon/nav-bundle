<?php

declare(strict_types=1);

namespace NavBundle\Bridge\ApiPlatform\DataProvider;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface CollectionExtensionInterface
{
    /**
     * Change criteria before retrieving a collection of entities.
     *
     * @param array $criteria the criteria
     * @param string $resourceClass the entity class
     * @param string|null $operationName the operation name
     * @param array $context the context
     *
     * @return void
     */
    public function applyToCollection(array $criteria, string $resourceClass, string $operationName = null, array $context = []);
}
