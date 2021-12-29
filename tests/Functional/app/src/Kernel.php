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

namespace NavBundle\App;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use NavBundle\NavBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),
            new ApiPlatformBundle(),
            new NavBundle(),
        ];

        if ($this->isDebug()) {
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/..';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log';
    }

    /**
     * @param RouteCollectionBuilder|RoutingConfigurator $routes
     */
    protected function configureRoutes($routes): void
    {
        $routes->import(__DIR__.'/../config/routes/annotations.yaml');
        $routes->import(__DIR__.'/../config/routes/api_platform.yaml');

        if ($this->isDebug()) {
            $routes->import(__DIR__.'/../config/routes/debug.yaml');
        }
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/../config/services.yaml');

        $c->loadFromExtension('framework', [
            'secret' => 'NavBundle',
            'test' => true,
            'router' => [
                'utf8' => true,
            ],
        ]);

        $c->loadFromExtension('twig', [
            'paths' => ['%kernel.project_dir%/templates'],
        ]);

        $c->loadFromExtension('sensio_framework_extra', [
            'request' => ['converters' => true, 'auto_convert' => true],
        ]);

        $c->loadFromExtension('api_platform', [
            'title' => 'NavBundle',
            'mapping' => [
                'paths' => ['%kernel.project_dir%/src/Entity'],
            ],
            'doctrine' => false,
            'collection' => [
                'pagination' => [
                    'client_items_per_page' => true,
                ],
            ],
        ]);

        $c->loadFromExtension('nav', [
            'enable_profiler' => '%kernel.debug%',
            'url' => $_SERVER['NAV_URL'],
            'paths' => [
                'App' => [
                    'path' => '%kernel.project_dir%/src/Entity',
                    'namespace' => 'NavBundle\App\Entity',
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
