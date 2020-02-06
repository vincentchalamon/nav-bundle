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

namespace NavBundle\Bridge\EasyAdminBundle\Search;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class RequestBuilder
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Creates the request builder used to get all the records displayed by the
     * "list" view.
     *
     * @param string|null $sortField
     * @param string|null $sortDirection
     *
     * @return RequestBuilderInterface
     */
    public function createListRequestBuilder(array $entityConfig, $sortField = null, $sortDirection = null, array $navFilter = [])
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($entityConfig['class']);
        $requestBuilder = $em->createRequestBuilder($entityConfig['class']);

        if (!empty($navFilter)) {
            foreach ($navFilter as $item => $value) {
                $requestBuilder->andWhere($item, $value);
            }
        }

        return $requestBuilder;
    }

    /**
     * Creates the request builder used to get the results of the search query
     * performed by the user in the "search" view.
     *
     * @param string      $searchQuery
     * @param string|null $sortField
     * @param string|null $sortDirection
     *
     * @return RequestBuilderInterface
     */
    public function createSearchRequestBuilder(array $entityConfig, $searchQuery, $sortField = null, $sortDirection = null, array $navFilter = [])
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($entityConfig['class']);
        /** @var ClassMetadataInterface $classMetadata */
        $classMetadata = $em->getClassMetadata($entityConfig['class']);
        /** @var RequestBuilderInterface $requestBuilder */
        $requestBuilder = $em->createRequestBuilder($entityConfig['class']);

        $searchableFields = array_keys($entityConfig['search']['fields']);
        if (1 < \count($searchableFields)) {
            throw new \InvalidArgumentException('Search on multiple fields is not supported.');
        } elseif (0 === \count($searchableFields)) {
            throw new \InvalidArgumentException('You must specify a search field.');
        }

        $fieldName = $searchableFields[0];

        if (!$classMetadata->hasAssociation($fieldName) && !$classMetadata->hasField($fieldName)) {
            throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field nor an association.");
        }

        $requestBuilder->andWhere($fieldName, $searchQuery);

        if (!empty($navFilter)) {
            foreach ($navFilter as $item => $value) {
                $requestBuilder->andWhere($item, $value);
            }
        }

        return $requestBuilder;
    }
}
