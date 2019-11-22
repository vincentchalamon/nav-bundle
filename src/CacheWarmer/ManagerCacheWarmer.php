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

namespace NavBundle\CacheWarmer;

use NavBundle\RegistryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\CompatibilityServiceSubscriberInterface as ServiceSubscriberInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ManagerCacheWarmer implements CacheWarmerInterface, ServiceSubscriberInterface
{
    private $container;
    private $registry;

    public function __construct(ContainerInterface $container)
    {
        // As this cache warmer is optional, dependencies should be lazy-loaded, that's why a container should be injected.
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        if (null === $this->registry) {
            $this->registry = $this->container->get('nav.registry');
        }

        if ($this->registry instanceof WarmableInterface) {
            $this->registry->warmUp($cacheDir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'registry' => RegistryInterface::class,
        ];
    }
}
