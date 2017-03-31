<?php

namespace Honeylex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Event\Bus\Channel\Channel;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventFilter;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventFilterList;
use Honeybee\Infrastructure\Event\Bus\Subscription\LazyEventSubscription;
use Honeybee\Infrastructure\Event\EventHandlerList;
use Honeybee\ServiceDefinitionInterface;
use Honeylex\Config\ConfigProviderInterface;
use Pimple\Container;

class EventBusProvisioner implements ProvisionerInterface
{
    const CONFIG_NAME = 'event_bus.yml';

    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $config = $configProvider->provide(self::CONFIG_NAME);

        $channelMap = new ChannelMap;
        foreach (array_keys($config['channels']) as $channelName) {
            $channelMap->setItem($channelName, new Channel($channelName));
        }

        $requiredChannels = array_diff($channelMap::getDefaultChannels(), $channelMap->getKeys());
        foreach ($requiredChannels as $defaultChannel) {
            $channelMap->setItem($defaultChannel, new Channel($defaultChannel));
        }

        $callback = function (EventBusInterface $eventBus) use ($injector, $config) {
            $this->prepareEventBus($injector, $eventBus, $config);
        };

        $service = $serviceDefinition->getClass();
        $state = [ ':channel_map' => $channelMap ];

        $injector
            ->define($service, $state)
            ->prepare($service, $callback)
            ->share($service)
            ->alias(EventBusInterface::CLASS, $service);
    }

    protected function prepareEventBus(Injector $injector, EventBusInterface $eventBus, array $config)
    {
        $builtTransports = [];

        foreach ($config['channels'] as $channelName => $subscriptions) {
            foreach ($subscriptions as $subscriptionConfig) {
                $eventHandlersFactory = function () use ($injector, $subscriptionConfig) {
                    return $this->buildEventHandlers($injector, $subscriptionConfig['handlers']);
                };

                $eventFiltersFactory = function () use ($injector, $subscriptionConfig) {
                    return $this->buildEventFilters($injector, $subscriptionConfig['filters']);
                };

                $eventTransportFactory = function () use (
                    $injector,
                    $subscriptionConfig,
                    $config,
                    $eventBus,
                    &$builtTransports
                ) {
                    $transportName = $subscriptionConfig['transport'];
                    if (!isset($builtTransports[$transportName])) {
                        $builtTransports[$transportName] = $this->buildTransport(
                            $injector,
                            $config['transports'],
                            $subscriptionConfig['transport'],
                            $eventBus
                        );
                    }

                    return $builtTransports[$transportName];
                };
                $subscriptionSettings = isset($subscriptionConfig['settings']) ? $subscriptionConfig['settings'] : [];
                $eventBus->subscribe(
                    $channelName,
                    new LazyEventSubscription(
                        $eventHandlersFactory,
                        $eventFiltersFactory,
                        $eventTransportFactory,
                        new Settings($subscriptionSettings),
                        $subscriptionConfig['enabled']
                    )
                );
            }
        }
    }

    protected function buildTransport(
        Injector $injector,
        array $transportConfigs,
        $transportName,
        EventBusInterface $eventBus
    ) {
        if (!isset($transportConfigs[$transportName])) {
            throw new ConfigError(
                sprintf('Unable to resolve config for transport: %s', $transportName)
            );
        }

        $transportConfig = $transportConfigs[$transportName];
        $transportState = [
            ':name' => $transportName,
            ':event_bus' => $eventBus
        ];
        $transportSettings = isset($transportConfig['settings']) ? $transportConfig['settings'] : [];
        foreach ($transportSettings as $key => $value) {
            $transportState[':' . $key] = $value;
        }

        return $injector->make($transportConfig['class'], $transportState);
    }

    protected function buildEventHandlers(Injector $injector, array $handlerConfigs)
    {
        $event_handlers = new EventHandlerList;

        foreach ($handlerConfigs as $handlerConfig) {
            $settings = isset($handlerConfig['settings']) ? $handlerConfig['settings'] : [];
            $event_handlers->addItem(
                $injector->make(
                    $handlerConfig['class'],
                    [ ':config' => new ArrayConfig($settings) ]
                )
            );
        }

        return $event_handlers;
    }

    protected function buildEventFilters(Injector $injector, array $filterConfigs)
    {
        // @todo make filter class configurable.
        $filterClass = EventFilter::class;
        $eventFilters = new EventFilterList;

        foreach ($filterConfigs as $filterConfig) {
            $eventFilters->addItem(
                $injector->make(
                    $filterClass,
                    [ ':settings' => new Settings($filterConfig['settings']) ]
                )
            );
        }

        return $eventFilters;
    }
}
