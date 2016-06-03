<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProvider;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Filesystem\Filesystem;

class TemplateRendererProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProvider $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $app->register(new TwigServiceProvider());
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            // add custom globals, filters, tags, ...
            return $twig;
        });

        $injector
            ->share($service)
            ->alias(TemplateRendererInterface::CLASS, $service)
            ->delegate(
                $service,
                function (Filesystem $filesystem) use ($service, $app) {
                    return new $service($app['twig'], $filesystem);
                }
            );
    }
}
