<?php

declare(strict_types=1);

namespace NavBundle\Bridge\ApiPlatform\DataProvider;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface ItemExtensionInterface
{
    /**
     * Change criteria before retrieving an entity.
     *
     * @param array $criteria the criteria
     * @param string $resourceClass the entity class
     * @param string $no the entity No
     * @param string|null $operationName the operation name
     * @param array $context the context
     *
     * @return void
     */
    public function applyToItem(array $criteria, string $resourceClass, string $no, string $operationName = null, array $context = []);
}
