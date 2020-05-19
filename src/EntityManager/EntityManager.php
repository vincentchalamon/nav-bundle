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

use Doctrine\Persistence\Mapping\ClassMetadata as DoctrineClassMetadataInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriver as MappingDriverInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\ClassMetadata\ClassMetadataFactory;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\Connection\ConnectionResolverInterface;
use NavBundle\EntityRepository\EntityRepositoryFactoryInterface;
use NavBundle\Event\EventManagerInterface;
use NavBundle\Exception\DeprecatedException;
use NavBundle\Exception\InvalidEntityNameException;
use NavBundle\Exception\InvalidObjectException;
use NavBundle\Exception\UnknownEntityNamespaceException;
use NavBundle\Hydrator\HydratorInterface;
use NavBundle\Hydrator\ObjectHydrator;
use NavBundle\RequestBuilder\RequestBuilder;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\UnitOfWork;
use NavBundle\Util\ClassUtils;
use ProxyManager\Proxy\LazyLoadingInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class EntityManager implements EntityManagerInterface
{
    protected $logger;
    protected $eventManager;
    protected $propertyAccessor;
    protected $connectionResolver;
    protected $mappingDriver;
    protected $nameConverter;
    protected $hydrators;
    protected $entityNamespaces = [];
    protected $unitOfWork;

    /**
     * @var EntityRepositoryFactoryInterface
     */
    protected $entityRepositoryFactory;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    public function __construct(
        ?LoggerInterface $logger,
        EventManagerInterface $eventManager,
        NormalizerInterface $normalizer,
        PropertyAccessorInterface $propertyAccessor,
        ContainerInterface $hydrators,
        ConnectionResolverInterface $connectionResolver,
        MappingDriverInterface $mappingDriver,
        NameConverterInterface $nameConverter,
        array $entityNamespaces
    ) {
        $this->logger = $logger ?: new NullLogger();
        $this->eventManager = $eventManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->hydrators = $hydrators;
        $this->connectionResolver = $connectionResolver;
        $this->mappingDriver = $mappingDriver;
        $this->nameConverter = $nameConverter;
        $this->entityNamespaces = $entityNamespaces;

        $this->unitOfWork = new UnitOfWork($this, $normalizer);

        $this->classMetadataFactory = new ClassMetadataFactory();
        $this->classMetadataFactory->setEntityManager($this);
    }

    public function setEntityRepositoryFactory(EntityRepositoryFactoryInterface $entityRepositoryFactory): void
    {
        $this->entityRepositoryFactory = $entityRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventManager(): EventManagerInterface
    {
        return $this->eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function find($className, $id): ?object
    {
        if (\is_object($id) && $this->classMetadataFactory->hasMetadataFor(ClassUtils::getRealClass($id))) {
            // TODO: Support find by association
            throw new \InvalidArgumentException('Find by association is not supported yet.');
        }

        $className = ltrim($className, '\\');
        if ($object = $this->unitOfWork->tryGetById($id, $className)) {
            return $object instanceof $className ? $object : null;
        }

        return $this->createRequestBuilder($className)->loadById($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidObjectException
     */
    public function persist($object): void
    {
        if (!\is_object($object)) {
            throw new InvalidObjectException('EntityManager#persist() expects parameter to be an entity object, '.\gettype($object).' given.');
        }

        $this->unitOfWork->persist($object);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidObjectException
     */
    public function remove($object): void
    {
        if (!\is_object($object)) {
            throw new InvalidObjectException('EntityManager#remove() expects parameter to be an entity object, '.\gettype($object).' given.');
        }

        $this->unitOfWork->remove($object);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DeprecatedException
     *
     * @return object
     */
    public function merge($object)
    {
        @trigger_error('Merge operation is deprecated and will be removed in doctrine/persistence 2.0.', E_USER_DEPRECATED);
        throw new DeprecatedException('Merge operation is deprecated and will be removed in doctrine/persistence 2.0.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws MappingException
     * @throws \ReflectionException
     */
    public function clear($objectName = null): void
    {
        if (null !== $objectName && !\is_string($objectName)) {
            throw new InvalidEntityNameException('Entity name must be a string, '.\gettype($objectName).' given');
        }

        $this->unitOfWork->clear(null === $objectName ? null : $this->getClassMetadata($objectName)->getName());
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidObjectException
     */
    public function detach($object): void
    {
        @trigger_error('Detach operation is deprecated and will be removed in doctrine/persistence 2.0.', E_USER_DEPRECATED);
        throw new DeprecatedException('Detach operation is deprecated and will be removed in doctrine/persistence 2.0.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidObjectException
     */
    public function refresh($object): void
    {
        if (!\is_object($object)) {
            throw new InvalidObjectException('EntityManager#refresh() expects parameter to be an entity object, '.\gettype($object).' given.');
        }

        $this->unitOfWork->refresh($object);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ExceptionInterface
     * @throws \SoapFault
     */
    public function flush($object = null): void
    {
        $this->unitOfWork->commit($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($className): ObjectRepository
    {
        return $this->entityRepositoryFactory->getRepository($this, $className);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MappingException
     * @throws \ReflectionException
     *
     * @return ClassMetadataInterface|DoctrineClassMetadataInterface
     */
    public function getClassMetadata($className)
    {
        return $this->classMetadataFactory->getMetadataFor($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory(): ClassMetadataFactory
    {
        return $this->classMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeObject($obj): void
    {
        if ($obj instanceof LazyLoadingInterface && !$obj->isProxyInitialized()) {
            $obj->initializeProxy();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function contains($object): bool
    {
        return $this->unitOfWork->isScheduledForInsert($object)
            || $this->unitOfWork->isInIdentityMap($object)
            && !$this->unitOfWork->isScheduledForDelete($object);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MappingException
     * @throws \ReflectionException
     */
    public function getConnection($className): ConnectionInterface
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->getClassMetadata($className);

        return $this->connectionResolver->resolve($classMetadata->getConnectionClass(), $classMetadata->getNamespace());
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownEntityNamespaceException
     */
    public function getEntityNamespace(string $alias): string
    {
        if (!isset($this->entityNamespaces[$alias])) {
            throw new UnknownEntityNamespaceException("Unknown entity namespace alias '$alias'.");
        }

        return $this->entityNamespaces[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDriver(): MappingDriverInterface
    {
        return $this->mappingDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameConverter(): NameConverterInterface
    {
        return $this->nameConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getHydrator(string $hydrator = null): HydratorInterface
    {
        if (null === $hydrator) {
            $hydrator = ObjectHydrator::class;
        }

        if (!$this->hydrators->has($hydrator)) {
            throw new \InvalidArgumentException("Hydrator $hydrator does not exist.");
        }

        return $this->hydrators->get($hydrator);
    }

    /**
     * {@inheritdoc}
     */
    public function createRequestBuilder($className): RequestBuilderInterface
    {
        return new RequestBuilder($this, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
