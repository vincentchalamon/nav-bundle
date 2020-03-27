<?php

declare(strict_types=1);

namespace NavBundle\App\Extension;

use NavBundle\App\Entity\Contact;
use NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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