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

namespace NavBundle\EntityManager;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\Event\EventManagerInterface;
use NavBundle\Hydrator\HydratorInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\UnitOfWork;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EntityManagerInterface extends ObjectManager
{
    /**
     * Gets the EventManager used by the EntityManager.
     *
     * @return EventManagerInterface the event manager
     */
    public function getEventManager(): EventManagerInterface;

    /**
     * Gets the PropertyAccessor used by the EntityManager.
     *
     * @return PropertyAccessorInterface the property accessor
     */
    public function getPropertyAccessor(): PropertyAccessorInterface;

    /**
     * Gets the UnitOfWork used by the EntityManager.
     *
     * @return UnitOfWork the unit of work
     */
    public function getUnitOfWork(): UnitOfWork;

    /**
     * Gets the RequestBuilder used by the EntityManager.
     *
     * @param string $className the entity name to create the request builder on
     *
     * @return RequestBuilderInterface the request builder
     */
    public function createRequestBuilder(string $className): RequestBuilderInterface;

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @param string $className the entity name
     *
     * @return ClassMetadataInterface the class metadata
     */
    public function getClassMetadata($className);

    /**
     * {@inheritdoc}
     *
     * @param object|array|null $object the entity or an array of entities to flush
     */
    public function flush($object = null);

    /**
     * Gets the connection used by the EntityManager.
     *
     * @param string $className the entity name
     *
     * @return ConnectionInterface the connection object
     */
    public function getConnection($className): ConnectionInterface;

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * @param string $namespaceAlias the entity namespace alias
     *
     * @return string the entity namespace
     */
    public function getEntityNamespace(string $namespaceAlias);

    /**
     * Get the MappingDriver.
     */
    public function getMappingDriver(): MappingDriver;

    /**
     * Get the NameConverter.
     */
    public function getNameConverter(): NameConverterInterface;

    /**
     * Get the Hydrator.
     *
     * @param string|null $hydrator the hydrator class name
     */
    public function getHydrator(string $hydrator = null): HydratorInterface;

    /**
     * Get the logger.
     */
    public function getLogger(): LoggerInterface;
}
