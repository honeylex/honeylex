<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\EventListener\HttpLocaleListener;
use Honeybee\FrameworkBinding\Silex\EventListener\SessionLocaleListener;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TranslationServiceProvisioner implements ProvisionerInterface, EventListenerProviderInterface
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
        $translationSettings = $configProvider->getSetting('project.translation', new Settings);

        $app->register(new LocaleServiceProvider);
        $app->register(
            new TranslationServiceProvider,
            [
                'locale' => $translationSettings->get('default_locale', 'en'),
                'locale_fallbacks' => (array) $translationSettings->get('locale_fallbacks', [ 'en' ])
            ]
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

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(
            new HttpLocaleListener($app['translator']->getLocale(), $app['translator']->getFallbackLocales())
        );
        $dispatcher->addSubscriber(new SessionLocaleListener($app['locale']));
    }
}
