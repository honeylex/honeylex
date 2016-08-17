<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Pimple\Container;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Fixture\FixtureServiceInterface;
use Honeybee\Infrastructure\Fixture\FixtureTarget;
use Honeybee\Infrastructure\Fixture\FixtureTargetMap;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;

class FixtureServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'fixture.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $factoryDelegate = function (
            AggregateRootTypeMap $aggregateRootTypeMap
        ) use (
            $injector,
            $configProvider,
            $serviceDefinition
        ) {
            $fixtureTargets = $configProvider->provide(self::CONFIG_NAME);
            $fixtureTargetMap = $this->buildFixtureTargetMap($injector, $fixtureTargets);

            $serviceConfig = $serviceDefinition->getConfig();
            $serviceClass = $serviceDefinition->getClass();

            return new $serviceClass($serviceConfig, $fixtureTargetMap, $aggregateRootTypeMap);
        };

        $service = $serviceDefinition->getClass();

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(FixtureServiceInterface::CLASS, $service);
    }

    protected function buildFixtureTargetMap(Injector $injector, array $config)
    {
        $fixtureTargets = [];

        foreach ($config as $targetName => $targetConfig) {
            $state = [
                ':name' => $targetName,
                ':is_activated' => $targetConfig['active'],
                ':fixture_loader' => $this->buildFixtureLoader($injector, $targetConfig['fixture_loader'])
            ];

            $fixtureTarget = $injector->make(FixtureTarget::CLASS, $state);
            $fixtureTargets[$targetName] = $fixtureTarget;
        }

        return new FixtureTargetMap($fixtureTargets);
    }

    protected function buildFixtureLoader(Injector $injector, array $config)
    {
        $class = $config['class'];

        if (!class_exists($class)) {
            throw new RuntimeError(sprintf('Unable to load configured collector class: %s', $class));
        }

        $state = [ ':config' => new ArrayConfig(isset($config['settings']) ? $config['settings'] : []) ];

        return $injector->make($class, $state);
    }
}
