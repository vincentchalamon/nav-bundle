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

namespace NavBundle\Bridge\ApiPlatform\DataProvider\Extension;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PaginationExtension implements CollectionExtensionInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function applyToCollection(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (null === $resourceClass) {
            throw new InvalidArgumentException('The "$resourceClass" parameter must not be null');
        }

        // TODO: Should be configurable.
        $builder->setFirstResult($this->requestStack->getCurrentRequest()->query->get('bookmarkKey'));
        // TODO: Should be configurable.
        $builder->setMaxResults(10);
    }
}
