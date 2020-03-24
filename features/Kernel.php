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
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new NavBundle\NavBundle(),
            new NavBundle\E2e\TestBundle\TestBundle(),
        ];

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

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
        $routes->import('@TestBundle/Controller', '', 'annotation');
        $routes->import('.', '', 'api_platform');
        if ($this->isDebug()) {
            $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml', '/_wdt');
            $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml', '/_profiler');
        }
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'NavBundle',
            'test' => true,
        ]);

        $c->loadFromExtension('twig', [
            'paths' => ['%kernel.project_dir%/TestBundle/Resources/views'],
        ]);

        $c->loadFromExtension('sensio_framework_extra', [
            'request' => ['converters' => true, 'auto_convert' => true],
        ]);

        $c->loadFromExtension('api_platform', [
            'title' => 'TestBundle',
            'mapping' => [
                'paths' => ['%kernel.project_dir%/TestBundle/Entity'],
            ],
            'doctrine' => false,
        ]);

        $c->loadFromExtension('nav', [
            'enable_profiler' => '%kernel.debug%',
            'url' => $_SERVER['NAV_URL'],
            'paths' => [
                'App' => [
                    'path' => '%kernel.project_dir%/TestBundle/Entity',
                    'namespace' => 'TestBundle\Entity',
                ],
            ],
        ]);

        if ($this->isDebug()) {
            $c->loadFromExtension('framework', [
                'profiler' => ['only_exceptions' => false],
            ]);

            $c->loadFromExtension('web_profiler', [
                'toolbar' => '%kernel.debug%',
            ]);
        }
    }
}
