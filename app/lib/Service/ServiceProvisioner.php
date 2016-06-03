<?php

namespace Honeybee\FrameworkBinding\Silex\Service;

use Auryn\Injector;
use Closure;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
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
use SplFileInfo;
use Trellis\CodeGen\Parser\Config\ConfigIniParser;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;
use Trellis\CodeGen\Schema\EntityTypeDefinition;
use Workflux\Builder\XmlStateMachineBuilder;

class ServiceProvisioner implements ServiceProvisionerInterface
{
    protected static $defaultProvisionerClass = DefaultProvisioner::CLASS;

    protected $app;

    protected $injector;

    protected $configProvider;

    protected $serviceDefinitions;

    public function __construct(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionMap $serviceDefinitions
    ) {
        $this->app = $app;
        $this->injector = $injector;
        $this->configProvider = $configProvider;
        $this->serviceDefinitions = $serviceDefinitions;
    }

    public function provision()
    {
        $this->registerEntityTypeMaps();
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

    protected function registerEntityTypeMaps()
    {
        $aggregateRootTypeMap = new AggregateRootTypeMap;
        $projectionTypeMap = new ProjectionTypeMap;

        foreach ($this->configProvider->getCrateMap() as $crate) {
            foreach (glob($crate->getConfigDir() . '/*/entity_schema/aggregate_root.xml') as $schemaFile) {
                $aggregateRootType = $this->loadEntityType($crate->getConfigDir(), $schemaFile);
                $aggregateRootTypeMap->setItem($aggregateRootType->getPrefix(), $aggregateRootType);
            }
            foreach (glob($crate->getConfigDir() . '/*/entity_schema/projection/*.xml') as $schemaFile) {
                $projectionType = $this->loadEntityType($crate->getConfigDir(), $schemaFile);
                $projectionTypeMap->setItem($projectionType->getPrefix(), $projectionType);
            }
        }
        $this->injector->share($aggregateRootTypeMap);
        $this->injector->share($projectionTypeMap);

        foreach ($aggregateRootTypeMap as $aggregateRootType) {
            $this->injector->share($aggregateRootType);
        }
        foreach ($projectionTypeMap as $projectionType) {
            $this->injector->share($projectionType);
        }
    }

    protected function evaluateServiceDefinitions()
    {
        $defaultProvisioner = static::$defaultProvisionerClass;

        foreach ($this->serviceDefinitions as $serviceKey => $serviceDefinition) {
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

    protected function evaluateServiceDefinition($serviceAlias, Closure $defaultProvisioning, array $settings = [])
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
            $defaultProvisioning($serviceDefinition);
        }
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
        $workflowFile = sprintf('%s/%s/workflows.xml', $crateConfigDir, $entityType->getName());
        $workflow = $this->loadWorkflow($entityType, $workflowFile);

        return new $class($workflow);
    }

    protected function loadWorkflow(EntityTypeDefinition $entityType, $workflowFile)
    {
        $vendor = $entityType->getOptions()->filterByName('vendor');
        $package = $entityType->getOptions()->filterByName('package');
        if (!$vendor || !$package) {
            throw new RuntimeError(
                'Missing vendor- and/or package-option for entity-type: ' . $entityType->getName()
            );
        }

        $builderConfig = [
            'state_machine_definition' => $workflowFile,
            'name' => sprintf(
                '%s_%s_%s_workflow_default',
                strtolower($vendor->getValue()),
                StringToolkit::asSnakeCase($package->getValue()),
                StringToolkit::asSnakeCase($entityType->getName())
            )
        ];

        return (new XmlStateMachineBuilder($builderConfig))->build();
    }
}
