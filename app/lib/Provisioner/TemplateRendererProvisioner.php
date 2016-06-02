<?php

namespace Honeybee\FrameworkBinding\Silex\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\App;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\ServiceDefinitionInterface;
use Silex\Provider\TwigServiceProvider;

class TemplateRendererProvisioner implements ProvisionerInterface
{
    public function provision(
        App $app,
        Injector $injector,
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
                function () use ($service, $app) {
                    return new $service([ ':twig' => $app['twig'] ]);
                }
            );
    }
}
