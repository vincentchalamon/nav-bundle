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
use NavBundle\Client\SoapClient;
use NavBundle\Event\PostCreateEvent;
use NavBundle\Event\PostDeleteEvent;
use NavBundle\Event\PostLoadEvent;
use NavBundle\Event\PostUpdateEvent;
use NavBundle\Event\PreCreateEvent;
use NavBundle\Event\PreDeleteEvent;
use NavBundle\Event\PreUpdateEvent;
use NavBundle\Exception\EntityNotFoundException;
use NavBundle\Repository\RepositoryInterface;
use NavBundle\Serializer\ObjectDecoder;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
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
    /**
     * @var iterable|RepositoryInterface[]
     */
    private $repositories;
    /**
     * @var iterable|\SoapClient[]
     */
    private $clients;
    private $wsdl;
    private $soapOptions;
    private $cacheDir;
    private $configCache;
    private $classMetadatas;

    public function __construct(
        ClassMetadataDriverInterface $driver,
        SerializerInterface $serializer,
        ConfigCacheFactoryInterface $configCacheFactory,
        EventDispatcherInterface $dispatcher,
        \IteratorAggregate $repositories,
        string $wsdl,
        array $soapOptions,
        string $cacheDir
    ) {
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->configCacheFactory = $configCacheFactory;
        $this->dispatcher = $dispatcher;
        $this->repositories = iterator_to_array($repositories->getIterator());
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
            throw new EntityNotFoundException("Entity $className not found.");
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
    public function getClient(string $className): \SoapClient
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
        if (!isset($this->repositories[$repositoryClass])) {
            $this->repositories[$repositoryClass] = new $repositoryClass($this, $className);
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $className, string $id): ?object
    {
        // todo Add logs
        // todo Get `No` by Id annotation
        $response = $this->getClient($className)->Read([
            'No' => $id,
        ]);
        $entity = $this->serializer->deserialize($response, $className, ObjectDecoder::FORMAT);
        $this->dispatcher->dispatch(new PostLoadEvent($this, $entity));

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $className): \Generator
    {
        return $this->findBy($className);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $className, array $criteria = [], int $size = 0): \Generator
    {
        // todo Add logs
        // todo Transform criteria ['name' => 'foo'] => [['Field' => 'Name', 'Criteria' => 'foo']]
        $entities = $this->serializer->deserialize($this->getClient($className)->ReadMultiple([
            'filter' => $criteria,
            'size' => $size,
        ]), $className.'[]', ObjectDecoder::FORMAT);

        foreach ($entities as $entity) {
            yield $entity;
            $this->dispatcher->dispatch(new PostLoadEvent($this, $entity));
        }
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
    public function create(object $entity): bool
    {
        // todo Add logs
        $this->dispatcher->dispatch(new PreCreateEvent($this, $entity));
        // todo Serialize data to NAV
        $this->getClient(\get_class($entity))->Create($this->serializer->serialize($entity, '???'));
        // todo Deserialize response
        // todo Set primary key on entity
        $this->dispatcher->dispatch(new PostCreateEvent($this, $entity));

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function update(object $entity): bool
    {
        // todo Add logs
        $this->dispatcher->dispatch(new PreUpdateEvent($this, $entity));
        // todo Serialize data to NAV
        $this->getClient(\get_class($entity))->Update($this->serializer->serialize($entity, '???'));
        // todo Deserialize response
        $this->dispatcher->dispatch(new PostUpdateEvent($this, $entity));

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(object $entity): bool
    {
        // todo Add logs
        $this->dispatcher->dispatch(new PreDeleteEvent($this, $entity));
        // todo Get Key from entity
        $this->getClient(\get_class($entity))->Delete([
            'Key' => $entity->getKey(),
        ]);
        // todo Deserialize response
        $this->dispatcher->dispatch(new PostDeleteEvent($this, $entity));

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        $this->getConfigCache();
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
