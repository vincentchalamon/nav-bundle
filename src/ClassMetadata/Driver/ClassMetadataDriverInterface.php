<?php

declare(strict_types=1);

namespace NavBundle\ClassMetadata\Driver;

use NavBundle\ClassMetadata\ClassMetadataInfoInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface ClassMetadataDriverInterface
{
    /**
     * Get entities class name and options.
     *
     * @param string $path The path where the entities configuration is stored.
     *
     * @return ClassMetadataInfoInterface[] The entities class name and options.
     */
    public function getEntities(string $path);
}