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

namespace NavBundle\App\Extension;

use NavBundle\App\Entity\Contact;
use NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ContactExtension implements CollectionExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyToCollection(RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (Contact::class === $resourceClass) {
            $requestBuilder->andWhere('type', 'Person');
        }
    }
}
