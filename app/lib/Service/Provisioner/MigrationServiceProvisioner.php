<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Migration\MigrationServiceInterface;
use Honeybee\Infrastructure\Migration\MigrationTarget;
use Honeybee\Infrastructure\Migration\MigrationTargetMap;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class MigrationServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'migration.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $factoryDelegate = function () use ($injector, $configProvider, $serviceDefinition) {
            $migrationTargets = $configProvider->provide(self::CONFIG_NAME);
            $migrationTargetMap = $this->buildMigrationTargetMap($injector, $migrationTargets);

            $serviceConfig = $serviceDefinition->getConfig();
            $serviceClass = $serviceDefinition->getClass();

            return new $serviceClass($serviceConfig, $migrationTargetMap);
        };

        $service = $serviceDefinition->getClass();

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(MigrationServiceInterface::CLASS, $service);
    }

    protected function buildMigrationTargetMap(Injector $injector, array $config)
    {
        $migrationTargetMap = new MigrationTargetMap();

        foreach ($config as $targetName => $targetConfig) {
            $state = [
                ':name' => $targetName,
                ':is_activated' => $targetConfig['active'],
                ':migration_loader' => $this->buildMigrationLoader($injector, $targetConfig['migration_loader']),
                ':config' => new ArrayConfig(isset($targetConfig['settings']) ? $targetConfig['settings'] : [])
            ];

            $migrationTarget = $injector->make(MigrationTarget::CLASS, $state);
            $migrationTargetMap->setItem($targetName, $migrationTarget);
        }

        return $migrationTargetMap;
    }

    protected function buildMigrationLoader(Injector $injector, array $config)
    {
        $class = $config['class'];
        if (!class_exists($class)) {
            throw new RuntimeError(sprintf("Unable to load configured collector class: %s", $class));
        }
        $state = [ ':config' => new ArrayConfig(isset($config['settings']) ? $config['settings'] : []) ];

        return $injector->make($class, $state);
    }
}
