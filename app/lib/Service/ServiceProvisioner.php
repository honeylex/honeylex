<?php

namespace Honeybee\FrameworkBinding\Silex\Service;

use Auryn\Injector;
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
        $serviceDefinitions = $this->configProvider->provide('services.yml');
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

    protected function registerEntityTypeMaps(ServiceDefinitionMap $serviceDefinitions)
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
