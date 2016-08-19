<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Mail\MailServiceInterface;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Silex\Provider\SwiftmailerServiceProvider;

class MailServiceProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'mail.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();
        $config = $configProvider->provide(self::CONFIG_NAME);

        $app->register(new SwiftmailerServiceProvider);

        if (isset($config['swiftmailer']['options'])) {
            $app['swiftmailer.options'] = $config['swiftmailer']['options'];
        }

        if (isset($config['swiftmailer']['use_spool'])) {
            $app['swiftmailer.use_spool'] = $config['swiftmailer']['use_spool'];
        }

        if (isset($config['swiftmailer']['delivery_addresses'])) {
            $app['swiftmailer.delivery_addresses'] = $config['swiftmailer']['delivery_addresses'];
        }

        if (isset($config['swiftmailer']['transport']) && class_exists($config['swiftmailer']['transport'])) {
            $app['swiftmailer.transport'] = new $config['swiftmailer']['transport'];
        }

        $injector
            ->share($service)
            ->alias(MailServiceInterface::CLASS, $service)
            ->delegate(
                $service,
                function (LoggerInterface $logger) use ($service, $app, $config) {
                    return new $service($app['mailer'], new ArrayConfig($config), $logger);
                }
            );
    }
}
