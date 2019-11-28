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

namespace NavBundle\Manager;

use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\ClassMetadata\Driver\ClassMetadataDriverInterface;
use NavBundle\Event\PostCreateEvent;
use NavBundle\Event\PostDeleteEvent;
use NavBundle\Event\PostLoadEvent;
use NavBundle\Event\PostUpdateEvent;
use NavBundle\Event\PreCreateEvent;
use NavBundle\Event\PreDeleteEvent;
use NavBundle\Event\PreUpdateEvent;
use NavBundle\Exception\ClassMetadataNotFoundException;
use NavBundle\Exception\KeyNotFoundException;
use NavBundle\Exception\MethodNotAllowedException;
use NavBundle\Exception\NoNotFoundException;
use NavBundle\Repository\RepositoryInterface;
use NavBundle\Serializer\ReadMultipleResultDecoder;
use NavBundle\Serializer\ReadResultDecoder;
use NavBundle\SoapClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Manager implements ManagerInterface, WarmableInterface
{
    private $driver;
    private $serializer;
    private $configCacheFactory;
    private $dispatcher;
    private $repositories;
    private $logger;
    private $wsdl;
    private $soapOptions;
    private $cacheDir;
    /**
     * @var RepositoryInterface[]
     */
    private $customRepositories = [];
    /**
     * @var SoapClient[]
     */
    private $clients;
    private $configCache;
    private $classMetadatas;

    /**
     * @param SerializerInterface|NormalizerInterface
     */
    public function __construct(
        ClassMetadataDriverInterface $driver,
        SerializerInterface $serializer,
        ConfigCacheFactoryInterface $configCacheFactory,
        EventDispatcherInterface $dispatcher,
        ContainerInterface $repositories,
        ?LoggerInterface $logger,
        string $wsdl,
        array $soapOptions,
        string $cacheDir
    ) {
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->configCacheFactory = $configCacheFactory;
        $this->dispatcher = $dispatcher;
        $this->repositories = $repositories;
        $this->logger = $logger ?: new NullLogger();
        $this->wsdl = $wsdl;
        $this->soapOptions = $soapOptions;
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata(string $className): ClassMetadataInterface
    {
        if (!isset($this->getClassMetadatas()[$className])) {
            throw new ClassMetadataNotFoundException("Entity $className not found.");
        }

        return $this->getClassMetadatas()[$className];
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(): ClassMetadataDriverInterface
    {
        return $this->driver;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(string $className): SoapClient
    {
        if (!isset($this->clients[$className])) {
            $this->clients[$className] = new SoapClient(
                $this->wsdl.$this->getClassMetadata($className)->getNamespace(),
                $this->soapOptions
            );
        }

        return $this->clients[$className];
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $className): RepositoryInterface
    {
        $repositoryClass = $this->getClassMetadata($className)->getRepositoryClass();
        if ($this->repositories->has($repositoryClass)) {
            // Repository is a service
            return $this->repositories->get($repositoryClass);
        }

        // Repository is not a service
        if (!isset($this->customRepositories[$repositoryClass])) {
            $this->repositories[$repositoryClass] = new $repositoryClass($this, $className);
        }

        return $this->customRepositories[$repositoryClass];
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $className, array $criteria = [], int $size = 0): iterable
    {
        $classMetadata = $this->getClassMetadata($className);
        $criteria = $this->getCriteria($classMetadata, $criteria);
        $this->logger->debug("Find $className objects.", [
            'className' => $className,
            'criteria' => $criteria,
            'size' => $size,
        ]);
        $entities = $this->serializer->deserialize(
            $this->getClient($className)->ReadMultiple([
                'filter' => $criteria,
                'setSize' => $size,
            ]),
            $className,
            ReadMultipleResultDecoder::FORMAT, [
                'namespace' => $classMetadata->getNamespace(),
            ]
        );
        if (!$entities) {
            return yield from [];
        }

        foreach ($entities as $entity) {
            $this->dispatcher->dispatch(new PostLoadEvent($this, $entity));
            yield $entity;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoNotFoundException
     */
    public function find(string $className, string $no): ?object
    {
        $classMetadata = $this->getClassMetadata($className);
        $criteria = [$classMetadata->getMapping()[$classMetadata->getNo()]['name'] => $no];
        $this->logger->debug("Find $className object #$no.", [
            'className' => $className,
            'criteria' => $criteria,
        ]);
        $entity = $this->serializer->deserialize(
            $this->getClient($className)->Read($criteria),
            $className,
            ReadResultDecoder::FORMAT, [
                'namespace' => $classMetadata->getNamespace(),
            ]
        );
        if (!$entity) {
            return null;
        }

        $this->dispatcher->dispatch(new PostLoadEvent($this, $entity));

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(string $className, array $criteria = []): ?object
    {
        return $this->findBy($className, $criteria, 1)->current();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $className): iterable
    {
        return $this->findBy($className);
    }

    /**
     * {@inheritdoc}
     */
    public function create(object $entity): void
    {
        $className = \get_class($entity);
        if (!$this->getClient($className)->__hasFunction('Create')) {
            throw new MethodNotAllowedException('Method "Create" is not allowed on this namespace.');
        }

        $this->dispatcher->dispatch(new PreCreateEvent($this, $entity));

        $data = $this->serializer->normalize($entity);
        $this->logger->debug("Create $className object.", ['data' => $data]);

        $this->serializer->deserialize(
            $this->getClient(\get_class($entity))->Create($data),
            $className,
            ReadResultDecoder::FORMAT, [
                'namespace' => $this->getClassMetadata($className)->getNamespace(),
                'object_to_populate' => $entity,
            ]
        );

        $this->dispatcher->dispatch(new PostCreateEvent($this, $entity));
    }

    // todo Implement CreateMultiple

    /**
     * {@inheritdoc}
     */
    public function update(object $entity): void
    {
        $className = \get_class($entity);
        if (!$this->getClient($className)->__hasFunction('Update')) {
            throw new MethodNotAllowedException('Method "Update" is not allowed on this namespace.');
        }

        $this->dispatcher->dispatch(new PreUpdateEvent($this, $entity));

        $data = $this->serializer->normalize($entity);
        $this->logger->debug("Update $className object.", ['data' => $data]);

        $this->serializer->deserialize(
            $this->getClient(\get_class($entity))->Update($data),
            $className,
            ReadResultDecoder::FORMAT, [
                'namespace' => $this->getClassMetadata($className)->getNamespace(),
                'object_to_populate' => $entity,
            ]
        );

        $this->dispatcher->dispatch(new PostUpdateEvent($this, $entity));
    }

    // todo Implement UpdateMultiple

    /**
     * {@inheritdoc}
     *
     * @throws KeyNotFoundException
     */
    public function delete(object $entity): void
    {
        $className = \get_class($entity);
        if (!$this->getClient($className)->__hasFunction('Delete')) {
            throw new MethodNotAllowedException('Method "Delete" is not allowed on this namespace.');
        }

        $this->dispatcher->dispatch(new PreDeleteEvent($this, $entity));

        $classMetadata = $this->getClassMetadata($className);
        $data = [$classMetadata->getMapping()[$classMetadata->getKey()]['name'] => $entity->key];
        $this->logger->debug("Delete $className object.", [
            'data' => $data,
        ]);

        $this->getClient(\get_class($entity))->Delete($data);

        $this->dispatcher->dispatch(new PostDeleteEvent($this, $entity));
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        $this->getConfigCache();
    }

    private function getCriteria(ClassMetadataInterface $classMetadata, array $filters): array
    {
        $mapping = $classMetadata->getMapping();
        $criteria = [];
        foreach ($filters as $key => $value) {
            $criteria[] = [
                'Field' => $mapping[$key]['name'],
                'Criteria' => $value,
            ];
        }

        return $criteria;
    }

    /**
     * @return ClassMetadataInterface[]
     */
    private function getClassMetadatas(): iterable
    {
        if (null !== $this->classMetadatas) {
            return $this->classMetadatas;
        }

        $mappings = require_once $this->getConfigCache()->getPath();
        foreach ($mappings as $className => $mapping) {
            $this->classMetadatas[$className] = new ClassMetadata(
                $mapping['repositoryClass'],
                $mapping['namespace'],
                $mapping['mapping']
            );
        }

        return $this->classMetadatas;
    }

    private function getConfigCache(): ConfigCacheInterface
    {
        if (null !== $this->configCache) {
            return $this->configCache;
        }

        $this->configCache = $this->configCacheFactory->cache($this->cacheDir.'/classMetadata.php', function (ConfigCacheInterface $cache): void {
            $cache->write(sprintf(<<<'PHP'
<?php

// This file has been auto-generated by the NavBundle for internal use.
// Returns the ClassMetadata infos.

return %s;
PHP
                , var_export($this->driver->getEntities(), true)));
        });

        return $this->configCache;
    }
}
