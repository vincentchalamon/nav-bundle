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

use NavBundle\Exception\ClassMetadataNotFoundException;
use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\RepositoryInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Registry implements RegistryInterface, WarmableInterface
{
    /**
     * @var iterable|ManagerInterface[]
     */
    private $managers;

    public function __construct(iterable $managers)
    {
        $this->managers = $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager(string $name = 'default'): ManagerInterface
    {
        if (!isset($this->managers[$name])) {
            throw new ManagerNotFoundException("Manager $name not found.");
        }

        return $this->managers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass(string $class): ManagerInterface
    {
        foreach ($this->managers as $manager) {
            try {
                $manager->getClassMetadata($class);

                return $manager;
            } catch (ClassMetadataNotFoundException $exception) {
                continue;
            }
        }

        throw new ManagerNotFoundException("No manager found for class $class.");
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $class): RepositoryInterface
    {
        return $this->getManager($class)->getRepository($class);
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        foreach ($this->managers as $manager) {
            if ($manager instanceof WarmableInterface) {
                $manager->warmUp($cacheDir);
            }
        }
    }
}
