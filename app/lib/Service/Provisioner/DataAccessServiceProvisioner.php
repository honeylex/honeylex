<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderMap;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkMap;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class DataAccessServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'data_access.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();
        $dataAccessConfig = $configProvider->provide(self::CONFIG_NAME);

        $this->registerWriterMapDelegate($injector, $dataAccessConfig['storage_writers']);
        $this->registerReaderMapDelegate($injector, $dataAccessConfig['storage_readers']);
        $this->registerUowMapDelegate($injector, $dataAccessConfig['units_of_work']);

        $this->registerFinderMapDelegate($injector, $dataAccessConfig['finders']);
        $this->registerQueryServiceMapDelegate($injector, $dataAccessConfig['query_services']);

        return $injector
            ->share($service)
            ->alias(DataAccessServiceInterface::CLASS, $service);
    }

    protected function registerWriterMapDelegate(Injector $injector, array $writerConfigs)
    {
        $injector->share(StorageWriterMap::CLASS)->delegate(
            StorageWriterMap::CLASS,
            function (ConnectorServiceInterface $connectorService) use ($injector, $writerConfigs) {
                $map = [];
                foreach ($writerConfigs as $writerKey => $writerConf) {
                    $objectState = [
                        ':config' => new ArrayConfig(isset($writerConf['settings']) ? $writerConf['settings'] : []),
                        ':connector' => $connectorService->getConnector($writerConf['connection'])
                    ];
                    if (isset($writerConf['dependencies'])) {
                        foreach ($writerConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map[$writerKey] = $injector->make($writerConf['class'], $objectState);
                }
                return new StorageWriterMap($map);
            }
        );
    }

    protected function registerReaderMapDelegate(Injector $injector, array $readerConfigs)
    {
        $injector->share(StorageReaderMap::CLASS)->delegate(
            StorageReaderMap::CLASS,
            function (ConnectorServiceInterface $connectorService) use ($injector, $readerConfigs) {
                $map = [];
                foreach ($readerConfigs as $readerKey => $readerConf) {
                    $objectState = [
                        ':config' => new ArrayConfig(isset($readerConf['settings']) ? $readerConf['settings'] : []),
                        ':connector' => $connectorService->getConnector($readerConf['connection'])
                    ];
                    if (isset($readerConf['dependencies'])) {
                        foreach ($readerConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map[$readerKey] = $injector->make($readerConf['class'], $objectState);
                }
                return new StorageReaderMap($map);
            }
        );
    }

    protected function registerFinderMapDelegate(Injector $injector, array $finderConfigs)
    {
        $injector->share(FinderMap::CLASS)->delegate(
            FinderMap::CLASS,
            function (ConnectorServiceInterface $connectorService) use ($injector, $finderConfigs) {
                $map = [];
                foreach ($finderConfigs as $finderKey => $finderConf) {
                    $objectState = [
                        ':config' => new ArrayConfig(isset($finderConf['settings']) ? $finderConf['settings'] : []),
                        ':connector' => $connectorService->getConnector($finderConf['connection'])
                    ];
                    if (isset($finderConf['dependencies'])) {
                        foreach ($finderConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map[$finderKey] = $injector->make($finderConf['class'], $objectState);
                }
                return new FinderMap($map);
            }
        );
    }

    protected function registerUowMapDelegate(Injector $injector, array $uowConfigs)
    {
        $injector->share(UnitOfWorkMap::CLASS)->delegate(
            UnitOfWorkMap::CLASS,
            function (
                StorageWriterMap $storageWriterMap,
                StorageReaderMap $storageReaderMap
            ) use (
                $injector,
                $uowConfigs
            ) {
                $map = [];
                foreach ($uowConfigs as $uowKey => $uowConf) {
                    $objectState = [
                        ':config' => new ArrayConfig(isset($uowConf['settings']) ? $uowConf['settings'] : []),
                        ':event_reader' => $storageReaderMap->getItem($uowConf['event_reader']),
                        ':event_writer' => $storageWriterMap->getItem($uowConf['event_writer'])
                    ];
                    if (isset($uowConf['dependencies'])) {
                        foreach ($uowConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map[$uowKey] = $injector->make($uowConf['class'], $objectState);
                }
                return new UnitOfWorkMap($map);
            }
        );
    }

    protected function registerQueryServiceMapDelegate(Injector $injector, array $qsConfigs)
    {
        $injector->share(QueryServiceMap::CLASS)->delegate(
            QueryServiceMap::CLASS,
            function (FinderMap $finderMap) use ($injector, $qsConfigs) {
                $map = [];
                foreach ($qsConfigs as $serviceKey => $qsConf) {
                    $finderMappings = [];
                    foreach ($qsConf['finder_mappings'] as $finderMappingName => $finderMapping) {
                        $finderMappings[$finderMappingName] = [
                            'finder' => $finderMap->getItem($finderMapping['finder']),
                            'query_translation' => $this->createQueryTranslation($finderMapping['query_translation'])
                        ];
                    }
                    $objectState =[
                        ':config' => new ArrayConfig(isset($qsConf['settings']) ? $qsConf['settings'] : []),
                        ':finder_mappings' => $finderMappings
                    ];
                    if (isset($qsConf['dependencies'])) {
                        foreach ($qsConf['dependencies'] as $key => $dependency) {
                            $objectState[$key] = $dependency;
                        }
                    }
                    $map[$serviceKey] = $injector->make($qsConf['class'], $objectState);
                }
                return new QueryServiceMap($map);
            }
        );
    }

    protected function createQueryTranslation(array $config)
    {
        $class = $config['class'];
        if (!$class) {
            throw new RuntimeError('Missing setting "query_translation" within ' . static::CLASS);
        }
        if (!class_exists($class)) {
            throw new RuntimeError(sprintf('Configured query-translation: "%s" does not exist!', $class));
        }
        $settings = isset($config['settings']) ? $config['settings'] : [];
        $queryTranslation = new $class(new ArrayConfig($settings));
        if (!$queryTranslation instanceof QueryTranslationInterface) {
            throw new RuntimeError(
                sprintf(
                    'Configured query-translation %s does not implement %s',
                    get_class($queryTranslation),
                    QueryTranslationInterface::CLASS
                )
            );
        }
        return $queryTranslation;
    }
}
