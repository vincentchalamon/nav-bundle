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

use Doctrine\Common\EventManager as EventManagerInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\Hydrator\HydratorInterface;
use NavBundle\NamingStrategy\NamingStrategyInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\UnitOfWork;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EntityManagerInterface extends ObjectManager
{
    /**
     * Gets the EventManageg used by the EntityManager.
     *
     * @return EventManagerInterface the event manager
     */
    public function getEventManager();

    /**
     * Gets the UnitOfWork used by the EntityManager.
     *
     * @return UnitOfWork the unit of work
     */
    public function getUnitOfWork();

    /**
     * Gets the RequestBuilder used by the EntityManager.
     *
     * @param string $className the entity name to create the request builder on
     *
     * @return RequestBuilderInterface the request builder
     */
    public function createRequestBuilder($className);

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @param string $className the entity name
     *
     * @return ClassMetadata the class metadata
     */
    public function getClassMetadata($className);

    /**
     * {@inheritdoc}
     *
     * @param object|array|null the entity or an array of entities to flush
     */
    public function flush($object = null);

    /**
     * Gets the connection used by the EntityManager.
     *
     * @param string $className the entity name
     *
     * @return ConnectionInterface the connection object
     */
    public function getConnection($className);

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
     *
     * @return MappingDriver
     */
    public function getMappingDriver();

    /**
     * Get the NamingStrategy.
     *
     * @return NamingStrategyInterface
     */
    public function getNamingStrategy();

    /**
     * Get the Hydrator.
     *
     * @return HydratorInterface
     */
    public function getHydrator();

    /**
     * Get the logger.
     *
     * @return LoggerInterface
     */
    public function getLogger();
}
