<?php

namespace Honeybee\FrameworkBinding\Silex\Service;

use Auryn\Injector;
use Closure;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProvider;
use Honeybee\FrameworkBinding\Silex\Service\Provisioner\DefaultProvisioner;
use Honeybee\FrameworkBinding\Silex\Service\Provisioner\ProvisionerInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Honeybee\ServiceLocator;
use Honeybee\ServiceLocatorInterface;
use Honeybee\ServiceProvisionerInterface;
use Pimple\Container;

class ServiceProvisioner implements ServiceProvisionerInterface
{
    protected static $defaultProvisionerClass = DefaultProvisioner::CLASS;

    protected $app;

    protected $injector;

    protected $configProvider;

    protected $serviceDefinitions;

    protected $aggregateRootTypeMap;

    protected $projectionTypeMap;

    protected $provisionedServices = [];

    public function __construct(
        Container $app,
        Injector $injector,
        ConfigProvider $configProvider,
        ServiceDefinitionMap $serviceDefinitions
    ) {
        $this->app = $app;
        $this->injector = $injector;
        $this->configProvider = $configProvider;
        $this->serviceDefinitions = $serviceDefinitions;
        $this->aggregateRootTypeMap = new AggregateRootTypeMap;
        $this->projectionTypeMap = new ProjectionTypeMap;
    }

    public function provision()
    {
        $this->injector->share($this->aggregateRootTypeMap);
        $this->injector->share($this->projectionTypeMap);

        foreach ($this->aggregateRootTypeMap as $aggregateRootType) {
            $this->injector->share($aggregateRootType);
        }
        foreach ($this->projectionTypeMap as $projectionType) {
            $this->injector->share($projectionType);
        }

        $this->evaluateServiceDefinitions();

        $serviceLocatorState = [
            ':service_definition_map' => $this->serviceDefinitions,
            ':di_container' => $this->injector
        ];

        return $this->injector
            ->share(ServiceLocator::CLASS)
            ->alias(ServiceLocatorInterface::CLASS, ServiceLocator::CLASS)
            ->make(ServiceLocator::CLASS, $serviceLocatorState);
    }

    protected function evaluateServiceDefinitions()
    {
        $remainingServices = array_diff($this->serviceDefinitions->getKeys(), $this->provisionedServices);
        $defaultProvisioner = static::$defaultProvisionerClass;

        foreach ($remainingServices as $serviceKey) {
            $serviceDefinition = $this->serviceDefinitions->getItem($serviceKey);
            $this->evaluateServiceDefinition(
                $serviceKey,
                function (ServiceDefinitionInterface $serviceDefinition) use ($defaultProvisioner) {
                    $this->injector
                        ->make($defaultProvisioner)
                        ->provision(
                            $this->app,
                            $this->injector,
                            $this->configProvider,
                            $serviceDefinition,
                            new Settings([])
                        );
                }
            );
        }
    }

    protected function evaluateServiceDefinition($serviceAlias, Closure $default_provisioning, array $settings = [])
    {
        if (!$this->serviceDefinitions->hasKey($serviceAlias)) {
            throw new RuntimeError(
                sprintf("Couldn't find service for key: %s. Maybe a typo within the services.xml?", $serviceAlias)
            );
        }

        $serviceDefinition = $this->serviceDefinitions->getItem($serviceAlias);
        if ($serviceDefinition->hasProvisioner()) {
            $this->runServiceProvisioner($serviceDefinition, $settings);
        } else {
            $default_provisioning($serviceDefinition);
        }

        $this->provisionedServices[] = $serviceAlias;
    }

    protected function runServiceProvisioner(ServiceDefinitionInterface $serviceDefinition, array $settings)
    {
        $provisionerConfig = $serviceDefinition->getProvisioner();
        if (!$provisionerConfig) {
            throw new RuntimeError(
                'Missing provisioner meta-data (at least "class" plus optional a "method" and some "settings").'
            );
        }
        if (!class_exists($provisionerConfig['class'])) {
            throw new RuntimeError('Unable to load provisioner class: ' . $provisionerConfig['class']);
        }

        $provisioner = $this->injector->make($provisionerConfig['class']);
        $provisionerMethod = $provisionerConfig['method'];
        $provisionerCallable = [ $provisioner, $provisionerMethod ];
        if (isset($provisionerConfig['settings']) && is_array($provisionerConfig['settings'])) {
            $settings = array_merge($provisionerConfig['settings'], $settings);
        }
        $provisioner_settings = new Settings($settings);

        if (!empty($provisionerMethod) && is_callable($provisionerCallable)) {
            $provisioner->$provisionerMethod($serviceDefinition, $provisioner_settings);
        } elseif ($provisioner instanceof ProvisionerInterface) {
            $provisioner->provision(
                $this->app,
                $this->injector,
                $this->configProvider,
                $serviceDefinition,
                $provisioner_settings
            );
        } else {
            throw new ConfigError(
                sprintf(
                    "Provisioner needs <method> configuration or must implement %s",
                    ProvisionerInterface::CLASS
                )
            );
        }
    }
}
