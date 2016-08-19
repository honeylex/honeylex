<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\EnvironmentInterface;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class EnvironmentProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();
        $state = [
            ':app' => $app,
            ':config' => $serviceDefinition->getConfig()
        ];

        $injector
            ->define($service, $state)
            ->share($service)
            ->alias(EnvironmentInterface::CLASS, $service);
    }
}
