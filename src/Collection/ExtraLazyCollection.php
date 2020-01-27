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

namespace NavBundle\Collection;

use NavBundle\Util\ClassUtils;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ExtraLazyCollection extends LazyCollection
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (!$this->isInitialized()) {
            $ownerClassName = ClassUtils::getRealClass($this->owner);
            $ownerClassMetadata = $this->registry->getManagerForClass($ownerClassName)->getClassMetadata($ownerClassName);
            $targetClass = $ownerClassMetadata->getAssociationTargetClass($this->associationName);

            return $this->registry->getManagerForClass($targetClass)->createRequestBuilder($targetClass)
                ->andWhere(
                    $ownerClassMetadata->getAssociationMappedByTargetField($this->associationName),
                    $ownerClassMetadata->getIdentifierValue($this->owner)
                )->count();
        }

        return parent::count();
    }
}
