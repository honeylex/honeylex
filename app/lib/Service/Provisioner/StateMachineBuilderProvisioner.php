<?php

namespace Honeylex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Workflow\StateMachineBuilderInterface;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceLocatorInterface;
use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Service\Provisioner\ProvisionerInterface;
use Pimple\Container;
use Workflux\Parser\Xml\StateMachineDefinitionParser;

class StateMachineBuilderProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $factoryDelegate = function (ServiceLocatorInterface $serviceLocator) use (
            $serviceDefinition,
            $configProvider
        ) {
            $stateMachineDefinitions = $this->loadStateMachineDefinitions($configProvider);
            $service = $serviceDefinition->getClass();
            return new $service($stateMachineDefinitions, $serviceLocator);
        };

        $service = $serviceDefinition->getClass();

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(StateMachineBuilderInterface::CLASS, $service);
    }

    protected function loadStateMachineDefinitions(ConfigProviderInterface $configProvider)
    {
        $stateMachineDefinitions = [];
        $parser = new StateMachineDefinitionParser;

        foreach ($configProvider->getCrateMap() as $crate) {
            foreach (glob($crate->getConfigDir().'/*/workflows.xml') as $workflowFile) {
                $stateMachineDefinitions = array_merge($stateMachineDefinitions, $parser->parse($workflowFile));
            }
        }

        return $stateMachineDefinitions;
    }
}
