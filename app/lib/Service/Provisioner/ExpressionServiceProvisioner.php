<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    ) {
        $service = $serviceDefinition->getClass();
        $state = [ ':expression_language' => new ExpressionLanguage ];

        $injector
            ->define($service, $state)
            ->share($service)
            ->alias(ExpressionServiceInterface::CLASS, $service);
    }
}
