<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class ConnectorServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'connections.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $injector
            ->share(ConnectorMap::CLASS)
            ->prepare(
                ConnectorMap::CLASS,
                function (ConnectorMap $map) use ($injector, $configProvider) {
                    $configs = $configProvider->provide(self::CONFIG_NAME);
                    $this->registerConnectors($injector, $configs, $map);
                }
            );

        $connectorService = $serviceDefinition->getClass();
        $serviceState = [ 'connector_map' => ConnectorMap::CLASS];

        return $injector
            ->define($connectorService, $serviceState)
            ->share($connectorService)
            ->alias(ConnectorServiceInterface::CLASS, $connectorService);
    }

    protected function registerConnectors(Injector $injector, array $configs, ConnectorMap $connectorMap)
    {
        foreach ($configs as $name => $config) {
            $connector = $config['class'];
            if (!class_exists($connector)) {
                throw new RuntimeError(sprintf('Unable to load configured connector class: %s', $connector));
            }

            $connectorState = [
                ':name' => $name,
                ':config' => new ArrayConfig($config['settings'])
            ];
            $dependencies = isset($config['dependencies']) ? $config['dependencies'] : [];
            foreach ($dependencies as $key => $dependency) {
                $connectorState[$key] = $dependency;
            }
            $connectorMap->setItem($name, $injector->make($connector, $connectorState));
        }
    }
}
