<?php

declare(strict_types=1);

namespace NavBundle\Bridge\ApiPlatform;

use ApiPlatform\Core\DataProvider\PartialPaginatorInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class Paginator implements PartialPaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage(): float
    {
        // TODO: Implement getCurrentPage() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): float
    {
        // TODO: Implement getItemsPerPage() method.
    }
}