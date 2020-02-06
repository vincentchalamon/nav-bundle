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

namespace NavBundle;

use Doctrine\Persistence\AbstractManagerRegistry;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Exception\InvalidMethodCallException;
use NavBundle\Exception\UnknownEntityNamespaceException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Registry extends AbstractManagerRegistry implements RegistryInterface, WarmableInterface
{
    private $container;

    public function __construct(ContainerInterface $container, array $managers, string $defaultManagerName, string $proxiesCacheDir)
    {
        $this->container = $container;

        if (!is_dir($proxiesCacheDir)) {
            mkdir($proxiesCacheDir, 0777, true);
        }

        parent::__construct('NAV', [], $managers, '', $defaultManagerName, LazyLoadingInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias): string
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                /** @var EntityManagerInterface $manager */
                $manager = $this->getManager($name);

                return $manager->getEntityNamespace($alias);
            } catch (\InvalidArgumentException $e) {
                // Ignore and continue.
            }
        }

        throw new UnknownEntityNamespaceException("Unknown entity namespace alias '$alias'.");
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections(): array
    {
        $connections = [];
        /** @var EntityManagerInterface[] $managers */
        $managers = $this->getManagers();
        foreach ($managers as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                $connections[$classMetadata->getName()] = $manager->getConnection($classMetadata->getName());
            }
        }

        return $connections;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     */
    public function getConnection($name = null): ConnectionInterface
    {
        throw new InvalidMethodCallException('Method getConnection() must not be called from Registry. You should invoke getManagerForClass($className)->getConnection($className).');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     *
     * @return string[]
     */
    public function getConnectionNames()
    {
        throw new InvalidMethodCallException('Method getConnectionNames() must not be called.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     *
     * @return string
     */
    public function getDefaultConnectionName()
    {
        throw new InvalidMethodCallException('Method getDefaultConnectionName() must not be called.');
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        // Warm up connections WSDL
        foreach ($this->getConnections() as $connection) {
            if ($connection instanceof WarmableInterface) {
                $connection->warmUp($cacheDir);
            }
        }

        // Warm up entities proxy
        /** @var LazyLoadingValueHolderFactory|null $holderFactory */
        $holderFactory = $this->getService('nav.proxy_manager.lazy_loading_value_holder_factory');
        if (null !== $holderFactory) {
            foreach ($this->getManagers() as $manager) {
                foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                    $holderFactory->createProxy($classMetadata->getName(), static function (): void {});
                }
            }
        }
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($name)
    {
        return $this->container->get($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function resetService($name): void
    {
        if (!$this->container->initialized($name)) {
            return;
        }

        // At this point, this method should not be called anywhere.
    }
}
