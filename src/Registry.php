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
use Doctrine\Persistence\Proxy;
use NavBundle\Exception\InvalidMethodCallException;
use NavBundle\Exception\UnknownEntityNamespaceException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Registry extends AbstractManagerRegistry implements RegistryInterface, WarmableInterface
{
    private $container;

    public function __construct(ContainerInterface $container, array $managers, string $defaultManagerName)
    {
        $this->container = $container;

        parent::__construct('NAV', [], $managers, '', $defaultManagerName, Proxy::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($name): object
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

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias): string
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getEntityNamespace($alias);
            } catch (\InvalidArgumentException $e) {
            }
        }

        throw new UnknownEntityNamespaceException("Unknown entity namespace alias '$alias'.");
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections(): iterable
    {
        foreach ($this->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                yield $manager->getConnection($classMetadata->getName());
            }
        }

        return yield from [];
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     */
    public function getConnection($name = null): void
    {
        throw new InvalidMethodCallException('Method getConnection() must not be called from Registry. You should invoke getManagerForClass($className)->getConnection($className).');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     */
    public function getConnectionNames(): void
    {
        throw new InvalidMethodCallException('Method getConnectionNames() must not be called.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMethodCallException
     */
    public function getDefaultConnectionName(): void
    {
        throw new InvalidMethodCallException('Method getDefaultConnectionName() must not be called.');
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        foreach ($this->getConnections() as $connection) {
            if ($connection instanceof WarmableInterface) {
                $connection->warmUp($cacheDir);
            }
        }
    }

    public function isOptional(): bool
    {
        return true;
    }
}
