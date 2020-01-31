<?php

declare(strict_types=1);

namespace NavBundle\EntityManager;

use Doctrine\Persistence\ObjectManagerDecorator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class EntityManagerDecorator extends ObjectManagerDecorator implements EntityManagerInterface
{
    public function __construct(EntityManagerInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventManager()
    {
        return $this->wrapped->getEventManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return $this->wrapped->getUnitOfWork();
    }

    /**
     * {@inheritdoc}
     */
    public function createRequestBuilder($className)
    {
        return $this->wrapped->createRequestBuilder($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($className)
    {
        return $this->wrapped->getConnection($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityNamespace(string $namespaceAlias)
    {
        return $this->wrapped->getEntityNamespace($namespaceAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDriver()
    {
        return $this->wrapped->getMappingDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function getNameConverter()
    {
        return $this->wrapped->getNameConverter();
    }

    /**
     * {@inheritdoc}
     */
    public function getHydrator(string $hydrator = null)
    {
        return $this->wrapped->getHydrator($hydrator);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->wrapped->getLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function flush($object = null)
    {
        $this->wrapped->flush($object);
    }
}