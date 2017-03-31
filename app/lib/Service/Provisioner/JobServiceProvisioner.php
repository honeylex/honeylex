<?php

namespace Honeylex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Job\JobMap;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceLocatorInterface;
use Honeylex\Config\ConfigProviderInterface;
use Pimple\Container;
use Psr\Log\LoggerInterface;

class JobServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'jobs.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $factoryDelegate = function (
            ConnectorServiceInterface $connectorService,
            ServiceLocatorInterface $serviceLocator,
            EventBusInterface $eventBus,
            LoggerInterface $logger
        ) use (
            $serviceDefinition,
            $provisionerSettings,
            $configProvider
        ) {
            $jobsConfig = $configProvider->provide(self::CONFIG_NAME);
            $connector = $connectorService->getConnector($provisionerSettings->get('connection'));
            $config = $serviceDefinition->getConfig();
            $service = $serviceDefinition->getClass();

            return new $service(
                $connector,
                $serviceLocator,
                $eventBus,
                new JobMap($jobsConfig),
                $config,
                $logger
            );
        };

        $service = $serviceDefinition->getClass();

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(JobServiceInterface::CLASS, $service);
    }
}
