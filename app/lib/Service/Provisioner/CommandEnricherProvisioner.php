<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Command\CommandEnricherInterface;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class CommandEnricherProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $factory = function (
            CommandEnricherInterface $commandEnricher,
            Injector $injector
        ) use (
            $provisionerSettings
        ) {
            foreach ((array) $provisionerSettings->get('enrichers') as $enricherClass) {
                $commandEnricher->addItem($injector->make($enricherClass));
            }
        };

        $injector
            ->prepare($service, $factory)
            ->share($service)
            ->alias(CommandEnricherInterface::CLASS, $service);
    }
}
