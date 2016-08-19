<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Service\Provisioner\ProvisionerInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class SilexServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();
        $serviceKey = $provisionerSettings->get('app_key');

        $injector->delegate($service, function () use ($app, $serviceKey) {
            return $app[$serviceKey];
        })->share($service);

        if ($provisionerSettings->has('alias')) {
            $alias = $provisionerSettings->get('alias');
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigError('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $service);
        }
    }
}
