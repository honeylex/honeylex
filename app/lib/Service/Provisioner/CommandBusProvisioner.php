<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\Bus\Subscription\LazyCommandSubscription;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

class CommandBusProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'command_bus.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $config = $configProvider->provide(self::CONFIG_NAME);

        $factory = function (CommandBusInterface $commandBus, Injector $injector) use ($config) {
            $this->prepareCommandBus($injector, $commandBus, $config);
        };

        $service = $serviceDefinition->getClass();

        $injector
            ->prepare($service, $factory)
            ->share($service)
            ->alias(CommandBusInterface::CLASS, $service);
    }

    protected function prepareCommandBus(Injector $injector, CommandBusInterface $commandBus, array $config)
    {
        $builtTransports = [];

        foreach ($config['transports'] as $transportName => $transportConfig) {
            if (!isset($builtTransports[$transportName])) {
                $builtTransports[$transportName] = $this->buildTransport(
                    $injector,
                    $transportName,
                    $transportConfig,
                    $commandBus
                );
            }
        }
        foreach ($config['subscriptions'] as $subscriptionConfig) {
            $transport = $builtTransports[$subscriptionConfig['transport']];
            foreach ($subscriptionConfig['commands'] as $commandType => $commandConfig) {
                $commandBus->subscribe(
                    $injector->make(
                        LazyCommandSubscription::CLASS,
                        [
                            ':command_type' => $commandType,
                            ':command_transport' => $transport,
                            ':command_handler_callback' => function () use ($injector, $commandConfig) {
                                return $injector->make($commandConfig['handler']);
                            }
                        ]
                    )
                );
            }
        }
    }

    protected function buildTransport(
        Injector $injector,
        $transportName,
        array $transportConfig,
        CommandBusInterface $commandBus
    ) {
        $transportState = [ ':name' => $transportName, ':command_bus' => $commandBus ];
        $settings = isset($transportConfig['settings']) ? $transportConfig['settings'] : [];
        foreach ($settings as $propName => $propValue) {
            $transportState[':' . $propName] = $propValue;
        }

        return $injector->make($transportConfig['class'], $transportState);
    }
}
