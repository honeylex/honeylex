<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
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
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $this->registerTwig($app, $injector, $provisionerSettings, $configProvider);

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

    protected function registerTwig(
        Container $app,
        Injector $injector,
        SettingsInterface $provisionerSettings,
        ConfigProviderInterface $configProvider
    ) {
        $app->register(new TwigServiceProvider);

        $namespacedPaths = $this->getCrateTemplatesPaths($configProvider);
        $projectTemplates = $configProvider->getProjectDir().'/app/templates';
        $namespacedPaths['honeylex'] = $configProvider->getCoreDir().'/app/templates';
        $namespacedPaths['project'] = $projectTemplates;

        $app['twig.form.templates'] = [ 'bootstrap_3_layout.html.twig' ];
        $app['twig.options'] = [ 'cache' => $configProvider->getProjectDir().'/var/cache/twig' ];
        $app['twig.loader.filesystem'] = function () use ($namespacedPaths, $projectTemplates) {
            $filesystem = new \Twig_Loader_Filesystem($projectTemplates);
            foreach ($namespacedPaths as $namespace => $path) {
                $filesystem->setPaths($path, $namespace);
            }
            return $filesystem;
        };

        $app['twig'] = $app->extend('twig', function ($twig, $app) use ($injector, $provisionerSettings) {
            foreach ($provisionerSettings->get('extensions', []) as $extension) {
                $twig->addExtension($injector->make($extension));
            }
            return $twig;
        });
    }

    protected function getCrateTemplatesPaths(ConfigProviderInterface $configProvider)
    {
        $projectDir = $configProvider->getProjectDir().'/app/templates';

        $paths = [];
        foreach ($configProvider->getCrateMap() as $crate) {
            $cratePrefix = $crate->getPrefix('-');
            $projectCratePath = $projectDir.'/'.$cratePrefix;
            if (is_readable($projectCratePath)) {
                $paths[$cratePrefix][] = $projectDir.'/'.$cratePrefix;
            }
            $templatesPath = $crate->getRootDir().'/templates';
            if (is_readable($templatesPath)) {
                $paths[$cratePrefix][] = $templatesPath;
            }
        }

        return $paths;
    }
}
