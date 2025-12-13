<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
        ];

        foreach ($contents as $bundle) {
            yield $bundle;
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');
        $container->import('../config/services.yaml');
        $devServicesFile = '../config/services_'.$this->environment.'.yaml';
        if (file_exists($devServicesFile)) {
            $container->import($devServicesFile);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/*.yaml');
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../src/Controller/', 'attribute');
    }
}
