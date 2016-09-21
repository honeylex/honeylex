<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Serializer\ProjectionNormalizer;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();

        $app->register(new SerializerServiceProvider);

        $app->extend(
            'serializer.encoders',
            function ($encoders, $app) use ($injector, $provisionerSettings) {
                foreach (array_reverse((array)$provisionerSettings->get('encoders', [])) as $encoder) {
                    array_unshift($encoders, $injector->make($encoder));
                }
                return $encoders;
            }
        );

        $app->extend(
            'serializer.normalizers',
            function ($normalizers, $app) use ($injector, $provisionerSettings) {
                foreach (array_reverse((array)$provisionerSettings->get('normalizers', [])) as $normalizer) {
                    array_unshift($normalizers, $injector->make($normalizer));
                }
                return $normalizers;
            }
        );

        $injector->delegate(
            $service,
            function () use ($app) {
                return $app['serializer'];
            }
        )
        ->share($service)
        ->alias(SerializerInterface::CLASS, $service);
    }
}
