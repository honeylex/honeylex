<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Pimple\Container;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorServiceInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\ServiceDefinitionInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

class FilesystemServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'filesystem.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $filesystemConfigs = $configProvider->provide(self::CONFIG_NAME);

        $factoryDelegate = function (
            AggregateRootTypeMap $aggregateRootTypeMap,
            ConnectorServiceInterface $connectorService
        ) use (
            $service,
            $filesystemConfigs
        ) {
            $filesystems = [];
            $connectors = [];
            $schemes = [];

            // check all configured filesystem connectors (they wrap a Filesystem instance with a specific adapter)
            foreach ($filesystemConfigs as $filesystemConfig) {
                $scheme = $filesystemConfig['scheme'];
                $connectorName = $filesystemConfig['connection'];
                $connectors[$scheme] = $connectorService->getConnector($connectorName);
                $schemes[$scheme] = $connectorName;
            }

            if (!array_key_exists(FilesystemServiceInterface::SCHEME_FILES, $schemes)) {
                throw new ConfigError(
                    sprintf(
                        'There is no filesystem connector registered for scheme "%s".',
                        FilesystemServiceInterface::SCHEME_FILES
                    )
                );
            }

            if (!array_key_exists(FilesystemServiceInterface::SCHEME_TEMPFILES, $schemes)) {
                throw new ConfigError(
                    sprintf(
                        'There is no filesystem connector registered for scheme "%s".',
                        FilesystemServiceInterface::SCHEME_TEMPFILES
                    )
                );
            }

            // reuse configured schemes for aggregate root specific file schemes
            foreach ($aggregateRootTypeMap->getKeys() as $typePrefix) {
                $filesScheme = $typePrefix.'.'.FilesystemServiceInterface::SCHEME_FILES;
                $tempfilesScheme = $typePrefix.'.'.FilesystemServiceInterface::SCHEME_TEMPFILES;
                if (!array_key_exists($filesScheme, $schemes)) {
                    $schemes[$filesScheme] = $connectors[FilesystemServiceInterface::SCHEME_FILES]->getName();
                }

                if (!array_key_exists($tempfilesScheme, $schemes)) {
                    $schemes[$tempfilesScheme] = $connectors[FilesystemServiceInterface::SCHEME_TEMPFILES]->getName();
                }
            }

            // get actual Filesystem instances for each scheme (they are configured and ready to use after this)
            foreach ($schemes as $scheme => $connectorName) {
                $filesystem = $connectorService->getConnection($connectorName);
                if (!$filesystem instanceof FilesystemInterface) {
                    throw new ConfigError(
                        sprintf(
                            'Filesystem connector for scheme "%s" must be an instance of: %s',
                            $scheme,
                            FilesystemInterface::CLASS
                        )
                    );
                }
                $filesystems[$scheme] = $filesystem;
            }

            $mountManager = new MountManager($filesystems);
            return new $service($mountManager, $schemes);
        };

        $injector
            ->delegate($service, $factoryDelegate)
            ->share($service)
            ->alias(FilesystemServiceInterface::CLASS, $service);
    }
}
