<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Job\JobMap;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceLocatorInterface;
use Psr\Log\LoggerInterface;
use Pimple\Container;
use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;

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
        $jobsConfig = $configProvider->provide(self::CONFIG_NAME);
        $service = $serviceDefinition->getClass();

        $factoryDelegate = function (
            ConnectorServiceInterface $connectorService,
            ServiceLocatorInterface $serviceLocator,
            EventBusInterface $eventBus,
            LoggerInterface $logger
        ) use (
            $serviceDefinition,
            $provisionerSettings,
            $jobsConfig
        ) {
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

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(JobServiceInterface::CLASS, $service);
    }
}
