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
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RequestBuilder
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function createListRequestBuilder(array $entityConfig, array $navFilter = []): RequestBuilderInterface
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

    public function createSearchRequestBuilder(array $entityConfig, $searchQuery, array $navFilter = []): RequestBuilderInterface
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

        $requestBuilder->andWhere($fieldName, "*$searchQuery*");

        if (!empty($navFilter)) {
            foreach ($navFilter as $item => $value) {
                $requestBuilder->andWhere($item, $value);
            }
        }

        return $requestBuilder;
    }
}
