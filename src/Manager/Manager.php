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

use matejsvajger\NTLMSoap\Client;
use matejsvajger\NTLMSoap\Common\NTLMConfig;
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\ClassMetadata\Driver\ClassMetadataDriverInterface;
use NavBundle\Exception\EntityNotFoundException;
use NavBundle\Repository\RepositoryInterface;
use NavBundle\Serializer\ObjectDecoder;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Manager implements ManagerInterface, WarmableInterface
{
    private $driver;
    private $serializer;
    private $configCacheFactory;
    private $repositories;
    private $clients;
    private $wsdl;
    private $options;
    private $soapOptions;
    private $cacheDir;
    private $cachedObjects = [];
    private $configCache;
    private $classMetadatas;

    public function __construct(
        ClassMetadataDriverInterface $driver,
        SerializerInterface $serializer,
        ConfigCacheFactoryInterface $configCacheFactory,
        \Traversable $repositories,
        \Traversable $clients,
        string $wsdl,
        array $options,
        array $soapOptions,
        string $cacheDir
    ) {
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->configCacheFactory = $configCacheFactory;
        $this->repositories = iterator_to_array($repositories);
        $this->clients = iterator_to_array($clients);
        $this->wsdl = $wsdl;
        $this->options = $options;
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
    public function getClient(string $className): Client
    {
        if (!isset($this->clients[$className])) {
            $this->clients[$className] = new Client(
                $this->wsdl.$this->getClassMetadata($className)->getNamespace(),
                new NTLMConfig($this->options),
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
    public function find(string $className, string $id)
    {
        if (!isset($this->cachedObjects[$className][$id])) {
            $this->cachedObjects[$className][$id] = $this->serializer->deserialize($this->getClient($className)->Read([
                'No' => $id,
            ]), $className, ObjectDecoder::FORMAT);
        }

        return $this->cachedObjects[$className][$id];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $className)
    {
        return $this->findBy($className);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $className, array $criteria = [], int $size = 0)
    {
        // todo Transform criteria ['name' => 'foo'] => [['Field' => 'Name', 'Criteria' => 'foo']]
        return $this->serializer->deserialize($this->getClient($className)->ReadMultiple([
            'filter' => $criteria,
            'size' => $size,
        ]), $className.'[]', ObjectDecoder::FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(string $className, array $criteria = [])
    {
        return $this->findBy($className, $criteria, 1)[0] ?? null;
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
