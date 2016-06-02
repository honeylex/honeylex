<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Closure;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Provisioner\DefaultProvisioner;
use Honeybee\FrameworkBinding\Silex\Provisioner\EnvironmentProvisioner;
use Honeybee\FrameworkBinding\Silex\Provisioner\ProvisionerInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Honeybee\ServiceLocatorInterface;
use Honeybee\ServiceProvisionerInterface;

class ServiceProvisioner implements ServiceProvisionerInterface
{
    protected static $defaultProvisionerClass = DefaultProvisioner::CLASS;

    protected static $defaultProvisioners = [
        'honeybee.environment' => EnvironmentProvisioner::CLASS,
    ];

    protected $injector;

    protected $serviceDefinitions;

    protected $aggregateRootTypeMap;

    protected $projectionTypeMap;

    protected $provisionedServices = [];

    public function __construct(Injector $injector, ServiceDefinitionMap $serviceDefinitions)
    {
        $this->injector = $injector;
        $this->serviceDefinitions = $serviceDefinitions;
        $this->aggregateRootTypeMap = new AggregateRootTypeMap;
        $this->projectionTypeMap = new ProjectionTypeMap;
    }

    public function provision()
    {
        $this->injector->share($this->serviceDefinitions);
        $this->injector->share($this->aggregateRootTypeMap);
        $this->injector->share($this->projectionTypeMap);

        foreach ($this->aggregateRootTypeMap as $aggregateRootType) {
            $this->injector->share($aggregateRootType);
        }
        foreach ($this->projectionTypeMap as $projectionType) {
            $this->injector->share($projectionType);
        }

        foreach (self::$defaultProvisioners as $serviceKey => $defaultProvisioner) {
            $this->provisionService(
                $serviceKey,
                function (ServiceDefinitionInterface $serviceDefinition) use ($defaultProvisioner) {
                    $this->injector->make($defaultProvisioner)->provision($this->injector, $serviceDefinition, new Settings([]));
                }
            );
        }

        $this->provisionServices();

        return $this->createServiceLocator();
    }

    protected function provisionServices()
    {
        $remainingServices = array_diff($this->serviceDefinitions->getKeys(), $this->provisionedServices);
        $defaultProvisioner = static::$defaultProvisionerClass;

        foreach ($remainingServices as $serviceKey) {
            $serviceDefinition = $this->serviceDefinitions->getItem($serviceKey);
            $this->provisionService(
                $serviceKey,
                function (ServiceDefinitionInterface $serviceDefinition) use ($defaultProvisioner) {
                    $this->injector->make($defaultProvisioner)->provision($this->injector, $serviceDefinition, new Settings([]));
                }
            );
        }
    }

    protected function provisionService($serviceAlias, Closure $default_provisioning, array $settings = [])
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
            $provisioner->provision($this->injector, $serviceDefinition, $provisioner_settings);
        } else {
            throw new ConfigError(
                sprintf(
                    "Provisioner needs <method> configuration or must implement %s",
                    ProvisionerInterface::CLASS
                )
            );
        }
    }

    protected function createServiceLocator()
    {
        $serviceDefinition = $this->serviceDefinitions->getItem('honeybee.service_locator');
        $service = $serviceDefinition->getClass();

        $this->injector->share($service)->alias(ServiceLocatorInterface::CLASS, $service);

        return $this->injector->make($service);
    }
}