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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new NavBundle\NavBundle(),
            new NavBundle\E2e\TestBundle\TestBundle(),
        ];

        return $bundles;
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'NavBundle',
            'secrets' => false,
            'test' => true,
        ]);

        $c->loadFromExtension('nav', [
            'wsdl' => $_SERVER['NAV_WSDL'],
            'path' => '%kernel.project_dir%/TestBundle/Entity',
            'soap_options' => [
                'cache_wsdl' => false,
                'trace' => true,
                'exception' => true,
                'domain' => 'CORUM',
                'username' => $_SERVER['NAV_LOGIN'],
                'password' => $_SERVER['NAV_PASSWORD'],
            ],
        ]);
    }
}
