<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;

class TranslationServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'translation.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $app->register(new LocaleServiceProvider);
        $app->register(
            new TranslationServiceProvider,
            [ 'locale_fallbacks' => [ 'en' ] ]
        );

        $this->registerResources($app, $configProvider);

        $injector->delegate($service, function () use ($app) {
            return $app['translator'];
        })->share($service);

        if ($provisionerSettings->has('alias')) {
            $alias = $provisionerSettings->get('alias');
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigError('Alias given must be an existing class or interface name (fully qualified).');
            }
            $injector->alias($alias, $service);
        }

        return $injector;
    }

    protected function registerResources(Container $app, ConfigProviderInterface $configProvider)
    {
        $configs = $configProvider->provide(self::CONFIG_NAME);

        $app->extend('translator.resources', function ($resources, $app) use ($configs) {
            foreach ($configs as $locale => $domains) {
                foreach ($domains as $domain => $translations) {
                    $resources[] = [ 'array', $translations, $locale, $domain ];
                }
            }
            return $resources;
        });
    }
}
