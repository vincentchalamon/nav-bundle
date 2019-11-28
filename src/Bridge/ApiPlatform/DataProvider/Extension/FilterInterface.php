<?php

declare(strict_types=1);

namespace NavBundle\Bridge\ApiPlatform\DataProvider\Extension;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface FilterInterface
{
    /**
     * Apply filter on collection criteria.
     *
     * @param array $criteria the criteria
     * @param string $resourceClass the entity resource class
     * @param string|null $operationName the operation name
     * @param array $context the context
     *
     * @return void
     */
    public function apply(array $criteria, string $resourceClass, string $operationName = null, array $context = []);
}
