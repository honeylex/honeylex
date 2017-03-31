<?php

namespace Honeylex\Service;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Honeybee\ServiceLocator;
use Honeybee\ServiceLocatorInterface;
use Honeybee\ServiceProvisionerInterface;
use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Service\Provisioner\DefaultProvisioner;
use Honeylex\Service\Provisioner\ProvisionerInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trellis\CodeGen\Parser\Config\ConfigIniParser;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;

class ServiceProvisioner implements ServiceProvisionerInterface
{
    protected static $defaultProvisionerClass = DefaultProvisioner::CLASS;

    protected $app;

    protected $injector;

    protected $configProvider;

    private $serviceDefinitions;

    public function __construct(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider
    ) {
        $this->app = $app;
        $this->injector = $injector;
        $this->configProvider = $configProvider;
    }

    public function provision()
    {
        $serviceDefinitions = $this->getServiceDefinitions();
        $this->registerEntityTypeMaps($serviceDefinitions);
        $this->evaluateServiceDefinitions($serviceDefinitions);

        $serviceLocatorState = [
            ':service_definition_map' => $serviceDefinitions,
            ':di_container' => $this->injector
        ];

        return $this->injector
            ->share(ServiceLocator::CLASS)
            ->alias(ServiceLocatorInterface::CLASS, ServiceLocator::CLASS)
            ->make(ServiceLocator::CLASS, $serviceLocatorState);
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $serviceDefinitions = $this->getServiceDefinitions();
        foreach ($serviceDefinitions as $key => $serviceDefinition) {
            if ($serviceDefinition->hasProvisioner()) {
                $provisionerConfig = $serviceDefinition->getProvisioner();
                $provisioner = $this->injector->make($provisionerConfig['class']);
                if ($provisioner instanceof EventListenerProviderInterface) {
                    $provisioner->subscribe($app, $dispatcher);
                }
            }
        }
    }

    protected function getServiceDefinitions()
    {
        if (!$this->serviceDefinitions) {
            $this->serviceDefinitions = $this->configProvider->provide('services.yml');
        }
        return $this->serviceDefinitions;
    }

    protected function registerEntityTypeMaps(ServiceDefinitionMap $serviceDefinitions)
    {
        $aggregateRootTypes = [];
        $projectionTypes = [];

        foreach ($this->configProvider->getCrateMap() as $crate) {
            foreach (glob($crate->getConfigDir().'/*/entity_schema/aggregate_root.xml') as $schemaFile) {
                $aggregateRootType = $this->loadEntityType($crate->getConfigDir(), $schemaFile);
                $aggregateRootTypes[$aggregateRootType->getPrefix()] = $aggregateRootType;
            }
            foreach (glob($crate->getConfigDir().'/*/entity_schema/projection/*.xml') as $schemaFile) {
                $projectionType = $this->loadEntityType($crate->getConfigDir(), $schemaFile);
                $projectionTypes[$projectionType->getVariantPrefix()] = $projectionType;
            }
        }

        $this->injector->share(new AggregateRootTypeMap($aggregateRootTypes));
        $this->injector->share(new ProjectionTypeMap($projectionTypes));

        foreach ($aggregateRootTypes as $aggregateRootType) {
            $this->injector->share($aggregateRootType);
        }

        foreach ($projectionTypes as $projectionType) {
            $this->injector->share($projectionType);
        }
    }

    protected function evaluateServiceDefinitions(ServiceDefinitionMap $serviceDefinitions)
    {
        $defaultProvisioner = $this->injector->make(static::$defaultProvisionerClass);
        foreach ($serviceDefinitions as $serviceKey => $serviceDefinition) {
            if ($serviceDefinition->hasProvisioner()) {
                $this->runServiceProvisioner($serviceDefinition);
            } else {
                $defaultProvisioner->provision(
                    $this->app,
                    $this->injector,
                    $this->configProvider,
                    $serviceDefinition,
                    new Settings([])
                );
            }
        }
    }

    protected function runServiceProvisioner(ServiceDefinitionInterface $serviceDefinition, array $settings = [])
    {
        $provisionerConfig = $serviceDefinition->getProvisioner();
        if (!$provisionerConfig) {
            throw new ConfigError(
                'Missing provisioner meta-data (at least "class" plus optional a "method" and some "settings").'
            );
        }
        if (!class_exists($provisionerConfig['class'])) {
            throw new ConfigError('Unable to load provisioner class: ' . $provisionerConfig['class']);
        }

        $provisioner = $this->injector->make($provisionerConfig['class']);
        $provisionerMethod = $provisionerConfig['method'];
        $provisionerCallable = [ $provisioner, $provisionerMethod ];
        if (isset($provisionerConfig['settings']) && is_array($provisionerConfig['settings'])) {
            $settings = array_merge($provisionerConfig['settings'], $settings);
        }
        $provisionerSettings = new Settings($settings);

        if (!empty($provisionerMethod) && is_callable($provisionerCallable)) {
            $provisioner->$provisionerMethod($serviceDefinition, $provisionerSettings);
        } elseif ($provisioner instanceof ProvisionerInterface) {
            $provisioner->provision(
                $this->app,
                $this->injector,
                $this->configProvider,
                $serviceDefinition,
                $provisionerSettings
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

    protected function loadEntityType($crateConfigDir, $schemaFile)
    {
        $schemaFile = new SplFileInfo($schemaFile);
        $iniParser = new ConfigIniParser;
        $config = $iniParser->parse(sprintf('%s/%s.ini', $schemaFile->getPath(), $schemaFile->getBasename('.xml')));
        $schema = (new EntityTypeSchemaXmlParser)->parse($schemaFile->getRealPath());
        $entityType = $schema->getEntityTypeDefinition();

        $class = sprintf('%s\\%s%s', $schema->getNamespace(), $entityType->getName(), $config->getTypeSuffix('Type'));

        return new $class;
    }
}
